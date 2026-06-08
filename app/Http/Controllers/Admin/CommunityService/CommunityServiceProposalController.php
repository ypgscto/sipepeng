<?php

namespace App\Http\Controllers\Admin\CommunityService;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityService\StoreCommunityServiceProposalRequest;
use App\Http\Requests\CommunityService\UpdateCommunityServiceProposalRequest;
use App\Models\ActivityLog;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\CommunityService\PkmBudgetItem;
use App\Models\Lppm\CommunityServiceScheme;
use App\Models\Lppm\Partner;
use App\Services\ActivityLogger;
use App\Services\CommunityService\PkmDocumentStorage;
use App\Services\CommunityService\PkmSiakadLookup;
use App\Services\CommunityService\PkmWorkflowService;
use App\Support\CommunityService\PkmPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityServiceProposalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = CommunityServiceProposal::query()
            ->visibleTo($user)
            ->with(['skema', 'mitra'])
            ->orderByDesc('updated_at');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($search): void {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('proposal_number', 'like', "%{$search}%")
                    ->orWhere('ketua_dosen_nama_snapshot', 'like', "%{$search}%")
                    ->orWhere('mitra_nama_snapshot', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $proposals = $query->paginate(20)->withQueryString();

        return view('admin.community-service.index', [
            'proposals' => $proposals,
            'filters' => [
                'q' => $request->query('q', ''),
                'status' => $request->query('status', ''),
            ],
            'statusOptions' => config('sipepeng_community_service.statuses', []),
            'canCreate' => PkmPermissions::canCreate($user),
        ]);
    }

    public function create(PkmSiakadLookup $lookup): View
    {
        return view('admin.community-service.create', $this->formData($lookup, auth()->user()));
    }

    public function store(
        StoreCommunityServiceProposalRequest $request,
        PkmDocumentStorage $storage,
        ActivityLogger $logger,
    ): RedirectResponse {
        $data = $request->validated();
        $number = PkmWorkflowService::generateProposalNumber();

        $proposal = CommunityServiceProposal::query()->create($this->proposalAttributes($data, $number, $request->user()));

        $this->handleUploads($request, $proposal, $storage);
        $this->syncBudgetItems($proposal, $data['budget_items'] ?? []);

        $logger->log('created', $proposal, 'Proposal PkM dibuat.', ['proposal_number' => $number], logName: 'lppm_pkm');

        return redirect()
            ->route('admin.community-service.show', $proposal)
            ->with('success', 'Proposal PkM berhasil dibuat.');
    }

    public function show(CommunityServiceProposal $proposal): View
    {
        $this->authorizeView($proposal);

        $proposal->load([
            'skema', 'mitra', 'jenisMitra', 'ketuaUser',
            'budgetItems', 'statusHistories.actor', 'adminVerifications.verifier',
            'reviews.reviewer.user', 'publications', 'ipRegistrations', 'letters',
        ]);

        $logs = ActivityLog::query()
            ->where('log_name', 'lppm_pkm')
            ->where('subject_type', $proposal->getMorphClass())
            ->where('subject_id', $proposal->id)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $user = auth()->user();

        return view('admin.community-service.show', [
            'proposal' => $proposal,
            'logs' => $logs,
            'canEdit' => PkmPermissions::canEdit($user, $proposal),
            'canSubmit' => PkmPermissions::canSubmit($user, $proposal),
            'canVerifyAdmin' => PkmPermissions::canVerifyAdmin($user) && $proposal->status === 'admin_pending',
            'canAssignReviewer' => PkmPermissions::canAssignReviewer($user) && $proposal->status === 'admin_verified',
            'canSubmitReview' => PkmPermissions::canSubmitReview($user, $proposal),
            'canDecide' => PkmPermissions::canDecide($user) && $proposal->status === 'review_completed',
            'reviewers' => \App\Models\Lppm\Reviewer::query()->active()->with('user')->get(),
            'myReview' => $proposal->reviews()
                ->whereHas('reviewer', fn ($q) => $q->where('user_id', $user->id))
                ->first(),
        ]);
    }

    public function edit(CommunityServiceProposal $proposal, PkmSiakadLookup $lookup): View
    {
        $this->authorizeView($proposal);
        abort_unless(PkmPermissions::canEdit(auth()->user(), $proposal), 403);

        $proposal->load('budgetItems');

        return view('admin.community-service.edit', array_merge(
            $this->formData($lookup, auth()->user()),
            ['proposal' => $proposal],
        ));
    }

    public function update(
        UpdateCommunityServiceProposalRequest $request,
        CommunityServiceProposal $proposal,
        PkmDocumentStorage $storage,
        ActivityLogger $logger,
    ): RedirectResponse {
        $data = $request->validated();
        $before = $proposal->toArray();

        $proposal->update($this->proposalAttributes($data, $proposal->proposal_number, $request->user(), $proposal));
        $this->handleUploads($request, $proposal, $storage);
        if ($request->has('budget_items')) {
            $this->syncBudgetItems($proposal, $data['budget_items'] ?? []);
        }

        $logger->log('updated', $proposal, 'Proposal PkM diperbarui.', [
            'before' => $before,
            'after' => $proposal->fresh()->toArray(),
        ], logName: 'lppm_pkm');

        return redirect()
            ->route('admin.community-service.show', $proposal)
            ->with('success', 'Proposal berhasil diperbarui.');
    }

    public function destroy(CommunityServiceProposal $proposal, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeView($proposal);
        abort_unless(PkmPermissions::canEdit(auth()->user(), $proposal), 403);
        abort_unless(in_array($proposal->status, ['draft', 'revision_required'], true), 403);

        $logger->log('deleted', $proposal, 'Draft proposal PkM dihapus.', [
            'proposal_number' => $proposal->proposal_number,
        ], logName: 'lppm_pkm');

        $proposal->delete();

        return redirect()
            ->route('admin.community-service.index')
            ->with('success', 'Draft proposal dihapus.');
    }

    public function submit(CommunityServiceProposal $proposal, PkmWorkflowService $workflow): RedirectResponse
    {
        $this->authorizeView($proposal);
        abort_unless(PkmPermissions::canSubmit(auth()->user(), $proposal), 403);
        abort_if($proposal->file_proposal === null, 422, 'Berkas proposal wajib diunggah.');
        abort_if($proposal->file_surat_mitra === null, 422, 'Surat mitra wajib diunggah.');

        $workflow->submit($proposal);

        return redirect()
            ->route('admin.community-service.show', $proposal)
            ->with('success', 'Proposal berhasil diajukan.');
    }

    public function download(CommunityServiceProposal $proposal, string $field, PkmDocumentStorage $storage)
    {
        $this->authorizeView($proposal);
        abort_unless(in_array($field, ['file_proposal', 'file_surat_mitra', 'file_pengesahan'], true), 404);

        return $storage->download($proposal->{$field}, $proposal->{$field.'_name'});
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(PkmSiakadLookup $lookup, $user): array
    {
        return [
            'schemes' => CommunityServiceScheme::query()->active()->ordered()->get(),
            'partners' => Partner::query()->active()->with('partnerType')->orderBy('name')->get(),
            'prodiOptions' => $lookup->prodi()['options'],
            'tahunAkademikOptions' => $lookup->tahunAkademik(),
            'semesterOptions' => $lookup->semester(),
            'defaultKetua' => [
                'id' => $user->siakad_login ?? $user->siakad_user_id ?? '',
                'nama' => $user->name,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function proposalAttributes(array $data, string $number, $user, ?CommunityServiceProposal $existing = null): array
    {
        $attrs = collect($data)->only([
            'tahun_akademik_id', 'tahun_akademik_nama_snapshot', 'semester_id', 'semester_nama_snapshot',
            'prodi_id', 'prodi_nama_snapshot', 'skema_id', 'judul', 'ketua_dosen_id', 'ketua_dosen_nama_snapshot',
            'mitra_id', 'mitra_nama_snapshot', 'jenis_mitra_id', 'jenis_mitra_nama_snapshot',
            'masalah_mitra', 'solusi_ditawarkan', 'target_capaian', 'metode_pelaksanaan',
            'lokasi_kegiatan', 'jadwal_mulai', 'jadwal_selesai', 'target_luaran',
        ])->all();

        $attrs['proposal_number'] = $number;
        $attrs['ketua_user_id'] = $user->id;
        $attrs['total_rab'] = $this->calculateTotalRab($data);
        $attrs['updated_by'] = $user->id;

        if ($existing === null) {
            $attrs['created_by'] = $user->id;
            $attrs['status'] = 'draft';
            $attrs['current_stage'] = 'submission';
        }

        return \App\Support\Proposal\ProposalLeaderAttributes::applyKetuaForUser($attrs, $user);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function calculateTotalRab(array $data): float
    {
        $items = $data['budget_items'] ?? [];
        if ($items !== []) {
            return round(collect($items)->sum(function (array $item): float {
                $qty = (float) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);

                return $qty * $price;
            }), 2);
        }

        return round((float) ($data['total_rab'] ?? 0), 2);
    }

    protected function handleUploads($request, CommunityServiceProposal $proposal, PkmDocumentStorage $storage): void
    {
        foreach (['file_proposal', 'file_surat_mitra', 'file_pengesahan'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }
            $storage->delete($proposal->{$field});
            $stored = $storage->store($request->file($field), $proposal->proposal_number, $field);
            $proposal->update([
                $field => $stored['path'],
                $field.'_name' => $stored['name'],
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    protected function syncBudgetItems(CommunityServiceProposal $proposal, array $items): void
    {
        $proposal->budgetItems()->delete();
        foreach ($items as $index => $item) {
            if (empty($item['item_name'])) {
                continue;
            }
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            PkmBudgetItem::query()->create([
                'community_service_proposal_id' => $proposal->id,
                'item_name' => $item['item_name'],
                'category' => $item['category'] ?? 'other',
                'quantity' => $qty,
                'unit' => $item['unit'] ?? null,
                'unit_price' => $price,
                'subtotal' => round($qty * $price, 2),
                'sort_order' => $index,
            ]);
        }
        $proposal->update(['total_rab' => $proposal->budgetItems()->sum('subtotal')]);
    }

    protected function authorizeView(CommunityServiceProposal $proposal): void
    {
        abort_unless(PkmPermissions::canView(auth()->user(), $proposal), 403);
    }
}
