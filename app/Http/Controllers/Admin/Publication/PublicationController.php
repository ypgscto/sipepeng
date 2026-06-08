<?php

namespace App\Http\Controllers\Admin\Publication;

use App\Http\Controllers\Admin\Concerns\FiltersLppmOutputRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publication\StorePublicationRequest;
use App\Http\Requests\Publication\UpdatePublicationRequest;
use App\Models\ActivityLog;
use App\Models\Lppm\OutputType;
use App\Models\Lppm\PublicationType;
use App\Models\Publication\Publication;
use App\Models\Publication\PublicationAuthor;
use App\Services\ActivityLogger;
use App\Services\Lppm\LppmSiakadLookup;
use App\Services\Publication\PublicationDocumentStorage;
use App\Services\Publication\PublicationWorkflowService;
use App\Support\Lppm\ProposalLinkHelper;
use App\Support\Publication\PublicationPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicationController extends Controller
{
    use FiltersLppmOutputRecords;

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $this->applyOutputFilters(
            Publication::query()->visibleTo($user)->with(['publicationType', 'authors']),
            $request,
        )->orderByDesc('updated_at');

        return view('admin.publications.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'tahun', 'prodi_id', 'source_type', 'dosen_id']),
            'statusOptions' => config('sipepeng_publication.statuses', []),
            'prodiOptions' => app(LppmSiakadLookup::class)->prodiOptions(),
            'canCreate' => PublicationPermissions::canCreate($user),
        ]);
    }

    public function create(LppmSiakadLookup $lookup, Request $request): View
    {
        return view('admin.publications.create', $this->formData($lookup, $request));
    }

    public function store(StorePublicationRequest $request, PublicationDocumentStorage $storage, ActivityLogger $logger): RedirectResponse
    {
        $data = ProposalLinkHelper::resolveSourceFields($request->validated());
        $number = PublicationWorkflowService::generateRegistrationNumber();
        $attrs = collect($data)->except(['authors'])->all();
        $attrs['registration_number'] = $number;
        $attrs['created_by'] = $request->user()->id;
        $attrs['updated_by'] = $request->user()->id;
        $attrs['status'] = 'draft';
        $attrs['current_stage'] = 'submission';

        $publication = Publication::query()->create($attrs);
        $this->syncAuthors($publication, $data['authors'] ?? []);
        $this->handleUploads($request, $publication, $storage);

        $logger->log('created', $publication, 'Publikasi dibuat.', ['registration_number' => $number], logName: 'lppm_publication');

        return redirect()->route('admin.publications.show', $publication)->with('success', 'Publikasi berhasil dibuat.');
    }

    public function show(Publication $publication): View
    {
        abort_unless(PublicationPermissions::canView(auth()->user(), $publication), 403);
        $publication->load(['publicationType', 'authors', 'researchProposal', 'communityServiceProposal', 'statusHistories.actor', 'verifications.verifier']);

        $logs = ActivityLog::query()->where('log_name', 'lppm_publication')
            ->where('subject_type', $publication->getMorphClass())->where('subject_id', $publication->id)
            ->orderByDesc('created_at')->limit(15)->get();

        $user = auth()->user();

        return view('admin.publications.show', [
            'record' => $publication,
            'logs' => $logs,
            'canEdit' => PublicationPermissions::canEdit($user, $publication),
            'canSubmit' => PublicationPermissions::canSubmit($user, $publication),
            'canVerify' => PublicationPermissions::canVerify($user) && $publication->status === 'admin_pending',
        ]);
    }

    public function edit(Publication $publication, LppmSiakadLookup $lookup, Request $request): View
    {
        abort_unless(PublicationPermissions::canEdit(auth()->user(), $publication), 403);
        $publication->load('authors');

        return view('admin.publications.edit', array_merge($this->formData($lookup, $request), ['record' => $publication]));
    }

    public function update(UpdatePublicationRequest $request, Publication $publication, PublicationDocumentStorage $storage, ActivityLogger $logger): RedirectResponse
    {
        $data = ProposalLinkHelper::resolveSourceFields($request->validated());
        $before = $publication->toArray();
        $attrs = collect($data)->except(['authors'])->all();
        $attrs['updated_by'] = $request->user()->id;
        $publication->update($attrs);
        $this->syncAuthors($publication, $data['authors'] ?? []);
        $this->handleUploads($request, $publication, $storage);
        $logger->log('updated', $publication, 'Publikasi diperbarui.', ['before' => $before], logName: 'lppm_publication');

        return redirect()->route('admin.publications.show', $publication)->with('success', 'Publikasi berhasil diperbarui.');
    }

    public function destroy(Publication $publication): RedirectResponse
    {
        abort_unless(PublicationPermissions::canEdit(auth()->user(), $publication), 403);
        abort_unless(in_array($publication->status, ['draft', 'revision_required'], true), 403);
        $publication->delete();

        return redirect()->route('admin.publications.index')->with('success', 'Draft publikasi dihapus.');
    }

    public function submit(Publication $publication, PublicationWorkflowService $workflow): RedirectResponse
    {
        abort_unless(PublicationPermissions::canSubmit(auth()->user(), $publication), 403);
        abort_if($publication->file_manuscript === null, 422, 'Manuskrip wajib diunggah.');
        $workflow->submit($publication);

        return redirect()->route('admin.publications.show', $publication)->with('success', 'Publikasi berhasil diajukan.');
    }

    public function download(Publication $publication, string $field, PublicationDocumentStorage $storage)
    {
        abort_unless(PublicationPermissions::canView(auth()->user(), $publication), 403);
        abort_unless(in_array($field, array_keys(config('sipepeng_publication.uploads', [])), true), 404);

        return $storage->download($publication->{$field}, $publication->{$field.'_name'});
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(LppmSiakadLookup $lookup, Request $request): array
    {
        $user = $request->user();

        return [
            'publicationTypes' => PublicationType::query()->active()->ordered()->get(),
            'outputTypes' => OutputType::query()->active()->ordered()->get(),
            'prodiOptions' => $lookup->prodiOptions(),
            'dosenOptions' => $lookup->dosenOptions(),
            'researchProposals' => ProposalLinkHelper::linkableResearchQuery($user)->get(['id', 'proposal_number', 'judul']),
            'pkmProposals' => ProposalLinkHelper::linkablePkmQuery($user)->get(['id', 'proposal_number', 'judul']),
            'prefillResearchId' => $request->query('research_proposal_id'),
            'prefillPkmId' => $request->query('community_service_proposal_id'),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $authors
     */
    protected function syncAuthors(Publication $publication, array $authors): void
    {
        $publication->authors()->delete();
        foreach ($authors as $index => $author) {
            if (empty($author['dosen_id'])) {
                continue;
            }
            PublicationAuthor::query()->create([
                'publication_id' => $publication->id,
                'author_order' => (int) ($author['author_order'] ?? $index + 1),
                'role' => $author['role'] ?? ($index === 0 ? 'lead' : 'co_author'),
                'dosen_id' => $author['dosen_id'],
                'dosen_nama_snapshot' => $author['dosen_nama_snapshot'],
                'prodi_id' => $author['prodi_id'] ?? null,
                'prodi_nama_snapshot' => $author['prodi_nama_snapshot'] ?? null,
            ]);
        }
    }

    protected function handleUploads($request, Publication $publication, PublicationDocumentStorage $storage): void
    {
        foreach (array_keys(config('sipepeng_publication.uploads', [])) as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }
            $storage->delete($publication->{$field});
            $stored = $storage->store($request->file($field), $publication->registration_number, $field);
            $publication->update([$field => $stored['path'], $field.'_name' => $stored['name']]);
        }
    }
}
