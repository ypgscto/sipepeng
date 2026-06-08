<?php

namespace App\Http\Controllers\Admin\ResearchEthics;

use App\Http\Controllers\Admin\Concerns\FiltersLppmOutputRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResearchEthics\AssignEthicsReviewerRequest;
use App\Http\Requests\ResearchEthics\StoreEthicsDecisionRequest;
use App\Http\Requests\ResearchEthics\StoreResearchEthicsApplicationRequest;
use App\Http\Requests\ResearchEthics\UpdateResearchEthicsApplicationRequest;
use App\Models\ActivityLog;
use App\Models\Research\ResearchProposal;
use App\Models\ResearchEthics\ResearchEthicsApplication;
use App\Services\ActivityLogger;
use App\Services\Lppm\LppmSiakadLookup;
use App\Services\ResearchEthics\EthicsDocumentStorage;
use App\Services\ResearchEthics\EthicsWorkflowService;
use App\Support\Research\ResearchPermissions;
use App\Support\ResearchEthics\EthicsPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchEthicsApplicationController extends Controller
{
    use FiltersLppmOutputRecords;

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $this->applyEthicsFilters(
            ResearchEthicsApplication::query()->visibleTo($user)->with('researchProposal'),
            $request,
        )->orderByDesc('updated_at');

        return view('admin.research-ethics.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'tahun', 'prodi_id', 'dosen_id']),
            'statusOptions' => config('sipepeng_ethics.statuses', []),
            'prodiOptions' => app(LppmSiakadLookup::class)->prodiOptions(),
            'canCreate' => EthicsPermissions::canCreate($user),
        ]);
    }

    public function create(LppmSiakadLookup $lookup, Request $request): View
    {
        $proposal = null;
        if ($id = $request->query('research_proposal_id')) {
            $proposal = ResearchProposal::query()->find($id);
            abort_unless($proposal && ResearchPermissions::canView($request->user(), $proposal), 403);
        }

        return view('admin.research-ethics.create', array_merge(
            ['prodiOptions' => $lookup->prodiOptions(), 'proposal' => $proposal],
            $proposal ? $this->prefillFromProposal($proposal, $request->user()) : [],
        ));
    }

    public function store(StoreResearchEthicsApplicationRequest $request, EthicsDocumentStorage $storage, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validated();
        $number = EthicsWorkflowService::generateApplicationNumber();
        $attrs = $data;
        $attrs['application_number'] = $number;
        $attrs['ketua_user_id'] = $request->user()->id;
        $attrs['created_by'] = $request->user()->id;
        $attrs['updated_by'] = $request->user()->id;
        $attrs['status'] = 'draft';
        $attrs['current_stage'] = 'submission';

        $application = ResearchEthicsApplication::query()->create($attrs);
        $this->handleUploads($request, $application, $storage);
        $logger->log('created', $application, 'Aplikasi etik dibuat.', ['application_number' => $number], logName: 'lppm_ethics');

        return redirect()->route('admin.research-ethics.show', $application)->with('success', 'Aplikasi etik berhasil dibuat.');
    }

    public function show(ResearchEthicsApplication $ethicsApplication): View
    {
        abort_unless(EthicsPermissions::canView(auth()->user(), $ethicsApplication), 403);
        $ethicsApplication->load(['researchProposal', 'reviews.reviewer.user', 'statusHistories.actor']);

        $logs = ActivityLog::query()->where('log_name', 'lppm_ethics')
            ->where('subject_type', $ethicsApplication->getMorphClass())->where('subject_id', $ethicsApplication->id)
            ->orderByDesc('created_at')->limit(15)->get();

        $user = auth()->user();

        return view('admin.research-ethics.show', [
            'record' => $ethicsApplication,
            'logs' => $logs,
            'canEdit' => EthicsPermissions::canEdit($user, $ethicsApplication),
            'canSubmit' => EthicsPermissions::canSubmit($user, $ethicsApplication),
            'canDecide' => EthicsPermissions::canDecide($user) && $ethicsApplication->status === 'committee_review',
            'canAssignReviewer' => $user->hasAnyRole(config('sipepeng_ethics.manage_roles', [])) && $ethicsApplication->status === 'committee_review',
            'reviewers' => \App\Models\Lppm\Reviewer::query()->active()->with('user')->get(),
        ]);
    }

    public function edit(ResearchEthicsApplication $ethicsApplication): View
    {
        abort_unless(EthicsPermissions::canEdit(auth()->user(), $ethicsApplication), 403);

        return view('admin.research-ethics.edit', ['record' => $ethicsApplication]);
    }

    public function update(UpdateResearchEthicsApplicationRequest $request, ResearchEthicsApplication $ethicsApplication, EthicsDocumentStorage $storage, ActivityLogger $logger): RedirectResponse
    {
        $attrs = $request->validated();
        $attrs['updated_by'] = $request->user()->id;
        $ethicsApplication->update($attrs);
        $this->handleUploads($request, $ethicsApplication, $storage);
        $logger->log('updated', $ethicsApplication, 'Aplikasi etik diperbarui.', logName: 'lppm_ethics');

        return redirect()->route('admin.research-ethics.show', $ethicsApplication)->with('success', 'Aplikasi etik berhasil diperbarui.');
    }

    public function destroy(ResearchEthicsApplication $ethicsApplication): RedirectResponse
    {
        abort_unless(EthicsPermissions::canEdit(auth()->user(), $ethicsApplication), 403);
        abort_unless(in_array($ethicsApplication->status, ['draft', 'revision_required'], true), 403);
        $ethicsApplication->delete();

        return redirect()->route('admin.research-ethics.index')->with('success', 'Draft aplikasi etik dihapus.');
    }

    public function submit(ResearchEthicsApplication $ethicsApplication, EthicsWorkflowService $workflow): RedirectResponse
    {
        abort_unless(EthicsPermissions::canSubmit(auth()->user(), $ethicsApplication), 403);
        abort_if($ethicsApplication->file_protocol === null || $ethicsApplication->file_ethics_application === null, 422, 'Protokol dan formulir etik wajib diunggah.');
        $workflow->submit($ethicsApplication);

        return redirect()->route('admin.research-ethics.show', $ethicsApplication)->with('success', 'Aplikasi etik berhasil diajukan.');
    }

    public function download(ResearchEthicsApplication $ethicsApplication, string $field, EthicsDocumentStorage $storage)
    {
        abort_unless(EthicsPermissions::canView(auth()->user(), $ethicsApplication), 403);
        abort_unless(in_array($field, array_keys(config('sipepeng_ethics.uploads', [])), true), 404);

        return $storage->download($ethicsApplication->{$field}, $ethicsApplication->{$field.'_name'});
    }

    /**
     * @return array<string, mixed>
     */
    protected function prefillFromProposal(ResearchProposal $proposal, $user): array
    {
        return [
            'prefill' => [
                'research_proposal_id' => $proposal->id,
                'proposal_number_snapshot' => $proposal->proposal_number,
                'proposal_judul_snapshot' => $proposal->judul,
                'ketua_dosen_id' => $proposal->ketua_dosen_id,
                'ketua_dosen_nama_snapshot' => $proposal->ketua_dosen_nama_snapshot,
                'prodi_id' => $proposal->prodi_id,
                'prodi_nama_snapshot' => $proposal->prodi_nama_snapshot,
            ],
        ];
    }

    protected function handleUploads($request, ResearchEthicsApplication $application, EthicsDocumentStorage $storage): void
    {
        foreach (array_keys(config('sipepeng_ethics.uploads', [])) as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }
            $storage->delete($application->{$field});
            $stored = $storage->store($request->file($field), $application->application_number, $field);
            $application->update([$field => $stored['path'], $field.'_name' => $stored['name']]);
        }
    }
}
