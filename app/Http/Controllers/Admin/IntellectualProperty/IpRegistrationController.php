<?php

namespace App\Http\Controllers\Admin\IntellectualProperty;

use App\Http\Controllers\Admin\Concerns\FiltersLppmOutputRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\IntellectualProperty\StoreIpRegistrationRequest;
use App\Http\Requests\IntellectualProperty\UpdateIpRegistrationRequest;
use App\Models\ActivityLog;
use App\Models\IntellectualProperty\IpInventor;
use App\Models\IntellectualProperty\IpRegistration;
use App\Models\Lppm\IpType;
use App\Services\ActivityLogger;
use App\Services\IntellectualProperty\IpDocumentStorage;
use App\Services\IntellectualProperty\IpWorkflowService;
use App\Services\Lppm\LppmSiakadLookup;
use App\Support\IntellectualProperty\IpPermissions;
use App\Support\Lppm\ProposalLinkHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IpRegistrationController extends Controller
{
    use FiltersLppmOutputRecords;

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $this->applyIpFilters(
            IpRegistration::query()->visibleTo($user)->with(['ipType', 'inventors']),
            $request,
        )->orderByDesc('updated_at');

        return view('admin.hki.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'tahun', 'prodi_id', 'source_type', 'dosen_id']),
            'statusOptions' => config('sipepeng_hki.statuses', []),
            'prodiOptions' => app(LppmSiakadLookup::class)->prodiOptions(),
            'canCreate' => IpPermissions::canCreate($user),
        ]);
    }

    public function create(LppmSiakadLookup $lookup, Request $request): View
    {
        return view('admin.hki.create', $this->formData($lookup, $request));
    }

    public function store(StoreIpRegistrationRequest $request, IpDocumentStorage $storage, ActivityLogger $logger): RedirectResponse
    {
        $data = ProposalLinkHelper::resolveSourceFields($request->validated());
        $number = IpWorkflowService::generateRegistrationNumber();
        $attrs = collect($data)->except(['inventors'])->all();
        $attrs['registration_number'] = $number;
        $attrs['created_by'] = $request->user()->id;
        $attrs['updated_by'] = $request->user()->id;
        $attrs['status'] = 'draft';
        $attrs['current_stage'] = 'submission';

        $registration = IpRegistration::query()->create($attrs);
        $this->syncInventors($registration, $data['inventors'] ?? []);
        $this->handleUploads($request, $registration, $storage);
        $logger->log('created', $registration, 'Pendaftaran HKI dibuat.', ['registration_number' => $number], logName: 'lppm_hki');

        return redirect()->route('admin.hki.show', $registration)->with('success', 'HKI berhasil dibuat.');
    }

    public function show(IpRegistration $ipRegistration): View
    {
        abort_unless(IpPermissions::canView(auth()->user(), $ipRegistration), 403);
        $ipRegistration->load(['ipType', 'inventors', 'researchProposal', 'communityServiceProposal', 'statusHistories.actor', 'verifications.verifier']);

        $logs = ActivityLog::query()->where('log_name', 'lppm_hki')
            ->where('subject_type', $ipRegistration->getMorphClass())->where('subject_id', $ipRegistration->id)
            ->orderByDesc('created_at')->limit(15)->get();

        $user = auth()->user();

        return view('admin.hki.show', [
            'record' => $ipRegistration,
            'logs' => $logs,
            'canEdit' => IpPermissions::canEdit($user, $ipRegistration),
            'canSubmit' => IpPermissions::canSubmit($user, $ipRegistration),
            'canVerify' => IpPermissions::canVerify($user) && $ipRegistration->status === 'admin_pending',
        ]);
    }

    public function edit(IpRegistration $ipRegistration, LppmSiakadLookup $lookup, Request $request): View
    {
        abort_unless(IpPermissions::canEdit(auth()->user(), $ipRegistration), 403);
        $ipRegistration->load('inventors');

        return view('admin.hki.edit', array_merge($this->formData($lookup, $request), ['record' => $ipRegistration]));
    }

    public function update(UpdateIpRegistrationRequest $request, IpRegistration $ipRegistration, IpDocumentStorage $storage, ActivityLogger $logger): RedirectResponse
    {
        $data = ProposalLinkHelper::resolveSourceFields($request->validated());
        $attrs = collect($data)->except(['inventors'])->all();
        $attrs['updated_by'] = $request->user()->id;
        $ipRegistration->update($attrs);
        $this->syncInventors($ipRegistration, $data['inventors'] ?? []);
        $this->handleUploads($request, $ipRegistration, $storage);
        $logger->log('updated', $ipRegistration, 'HKI diperbarui.', logName: 'lppm_hki');

        return redirect()->route('admin.hki.show', $ipRegistration)->with('success', 'HKI berhasil diperbarui.');
    }

    public function destroy(IpRegistration $ipRegistration): RedirectResponse
    {
        abort_unless(IpPermissions::canEdit(auth()->user(), $ipRegistration), 403);
        abort_unless(in_array($ipRegistration->status, ['draft', 'revision_required'], true), 403);
        $ipRegistration->delete();

        return redirect()->route('admin.hki.index')->with('success', 'Draft HKI dihapus.');
    }

    public function submit(IpRegistration $ipRegistration, IpWorkflowService $workflow): RedirectResponse
    {
        abort_unless(IpPermissions::canSubmit(auth()->user(), $ipRegistration), 403);
        abort_if($ipRegistration->file_application === null, 422, 'Berkas permohonan wajib diunggah.');
        $workflow->submit($ipRegistration);

        return redirect()->route('admin.hki.show', $ipRegistration)->with('success', 'HKI berhasil diajukan.');
    }

    public function download(IpRegistration $ipRegistration, string $field, IpDocumentStorage $storage)
    {
        abort_unless(IpPermissions::canView(auth()->user(), $ipRegistration), 403);
        abort_unless(in_array($field, array_keys(config('sipepeng_hki.uploads', [])), true), 404);

        return $storage->download($ipRegistration->{$field}, $ipRegistration->{$field.'_name'});
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(LppmSiakadLookup $lookup, Request $request): array
    {
        $user = $request->user();

        return [
            'ipTypes' => IpType::query()->active()->ordered()->get(),
            'prodiOptions' => $lookup->prodiOptions(),
            'dosenOptions' => $lookup->dosenOptions(),
            'researchProposals' => ProposalLinkHelper::linkableResearchQuery($user)->get(['id', 'proposal_number', 'judul']),
            'pkmProposals' => ProposalLinkHelper::linkablePkmQuery($user)->get(['id', 'proposal_number', 'judul']),
            'prefillResearchId' => $request->query('research_proposal_id'),
            'prefillPkmId' => $request->query('community_service_proposal_id'),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $inventors
     */
    protected function syncInventors(IpRegistration $registration, array $inventors): void
    {
        $registration->inventors()->delete();
        foreach ($inventors as $index => $inventor) {
            if (empty($inventor['dosen_id'])) {
                continue;
            }
            IpInventor::query()->create([
                'ip_registration_id' => $registration->id,
                'inventor_order' => (int) ($inventor['inventor_order'] ?? $index + 1),
                'dosen_id' => $inventor['dosen_id'],
                'dosen_nama_snapshot' => $inventor['dosen_nama_snapshot'],
                'prodi_id' => $inventor['prodi_id'] ?? null,
                'prodi_nama_snapshot' => $inventor['prodi_nama_snapshot'] ?? null,
            ]);
        }
    }

    protected function handleUploads($request, IpRegistration $registration, IpDocumentStorage $storage): void
    {
        foreach (array_keys(config('sipepeng_hki.uploads', [])) as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }
            $storage->delete($registration->{$field});
            $stored = $storage->store($request->file($field), $registration->registration_number, $field);
            $registration->update([$field => $stored['path'], $field.'_name' => $stored['name']]);
        }
    }
}
