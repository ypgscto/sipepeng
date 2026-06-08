<?php

namespace App\Http\Controllers\Admin\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Research\StoreResearchProposalRequest;
use App\Http\Requests\Research\UpdateResearchProposalRequest;
use App\Models\ActivityLog;
use App\Models\Lppm\FocusArea;
use App\Models\Lppm\ResearchScheme;
use App\Models\Lppm\ScienceCluster;
use App\Models\Research\ResearchBudgetItem;
use App\Models\Research\ResearchProposal;
use App\Services\ActivityLogger;
use App\Services\Research\ResearchDocumentStorage;
use App\Services\Research\ResearchSiakadLookup;
use App\Services\Research\ResearchWorkflowService;
use App\Support\Research\ResearchPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchProposalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = ResearchProposal::query()
            ->visibleTo($user)
            ->with(['skema', 'bidangFokus', 'rumpunIlmu'])
            ->orderByDesc('updated_at');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($search): void {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('proposal_number', 'like', "%{$search}%")
                    ->orWhere('ketua_dosen_nama_snapshot', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $proposals = $query->paginate(20)->withQueryString();

        return view('admin.research.index', [
            'proposals' => $proposals,
            'filters' => [
                'q' => $request->query('q', ''),
                'status' => $request->query('status', ''),
            ],
            'statusOptions' => config('sipepeng_research.statuses', []),
            'canCreate' => ResearchPermissions::canCreate($user),
        ]);
    }

    public function create(ResearchSiakadLookup $lookup): View
    {
        $user = auth()->user();

        return view('admin.research.create', $this->formData($lookup, $user));
    }

    public function store(
        StoreResearchProposalRequest $request,
        ResearchDocumentStorage $storage,
        ResearchWorkflowService $workflow,
        ActivityLogger $logger,
    ): RedirectResponse {
        $data = $request->validated();
        $number = ResearchWorkflowService::generateProposalNumber();

        $proposal = ResearchProposal::query()->create($this->proposalAttributes($data, $number, $request->user()));

        $this->handleUploads($request, $proposal, $storage, $data);
        $this->syncBudgetItems($proposal, $data['budget_items'] ?? []);

        $logger->log('created', $proposal, 'Proposal penelitian dibuat.', ['proposal_number' => $number], logName: 'lppm_research');

        return redirect()
            ->route('admin.research.show', $proposal)
            ->with('success', 'Proposal penelitian berhasil dibuat.');
    }

    public function show(ResearchProposal $proposal): View
    {
        $this->authorizeView($proposal);

        $proposal->load([
            'skema', 'bidangFokus', 'rumpunIlmu', 'ketuaUser',
            'budgetItems', 'statusHistories.actor', 'adminVerifications.verifier',
            'reviews.reviewer.user', 'publications', 'ipRegistrations', 'ethicsApplications', 'letters',
        ]);

        $logs = ActivityLog::query()
            ->where('log_name', 'lppm_research')
            ->where('subject_type', $proposal->getMorphClass())
            ->where('subject_id', $proposal->id)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $user = auth()->user();

        return view('admin.research.show', [
            'proposal' => $proposal,
            'logs' => $logs,
            'canEdit' => ResearchPermissions::canEdit($user, $proposal),
            'canSubmit' => ResearchPermissions::canSubmit($user, $proposal),
            'canVerifyAdmin' => ResearchPermissions::canVerifyAdmin($user) && $proposal->status === 'admin_pending',
            'canAssignReviewer' => ResearchPermissions::canAssignReviewer($user) && $proposal->status === 'admin_verified',
            'canSubmitReview' => ResearchPermissions::canSubmitReview($user, $proposal),
            'canDecide' => ResearchPermissions::canDecide($user) && $proposal->status === 'review_completed',
            'reviewers' => \App\Models\Lppm\Reviewer::query()->active()->with('user')->get(),
            'myReview' => $proposal->reviews()
                ->whereHas('reviewer', fn ($q) => $q->where('user_id', $user->id))
                ->first(),
        ]);
    }

    public function edit(ResearchProposal $proposal, ResearchSiakadLookup $lookup): View
    {
        $this->authorizeView($proposal);
        abort_unless(ResearchPermissions::canEdit(auth()->user(), $proposal), 403);

        $proposal->load('budgetItems');

        return view('admin.research.edit', array_merge(
            $this->formData($lookup, auth()->user()),
            ['proposal' => $proposal],
        ));
    }

    public function update(
        UpdateResearchProposalRequest $request,
        ResearchProposal $proposal,
        ResearchDocumentStorage $storage,
        ActivityLogger $logger,
    ): RedirectResponse {
        $data = $request->validated();
        $before = $proposal->toArray();

        $proposal->update($this->proposalAttributes($data, $proposal->proposal_number, $request->user(), $proposal));
        $this->handleUploads($request, $proposal, $storage, $data);
        if ($request->has('budget_items')) {
            $this->syncBudgetItems($proposal, $data['budget_items'] ?? []);
        }

        $logger->log('updated', $proposal, 'Proposal penelitian diperbarui.', [
            'before' => $before,
            'after' => $proposal->fresh()->toArray(),
        ], logName: 'lppm_research');

        return redirect()
            ->route('admin.research.show', $proposal)
            ->with('success', 'Proposal berhasil diperbarui.');
    }

    public function destroy(ResearchProposal $proposal, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeView($proposal);
        abort_unless(ResearchPermissions::canEdit(auth()->user(), $proposal), 403);
        abort_unless(in_array($proposal->status, ['draft', 'revision_required'], true), 403);

        $logger->log('deleted', $proposal, 'Draft proposal dihapus.', [
            'proposal_number' => $proposal->proposal_number,
        ], logName: 'lppm_research');

        $proposal->delete();

        return redirect()
            ->route('admin.research.index')
            ->with('success', 'Draft proposal dihapus.');
    }

    public function submit(ResearchProposal $proposal, ResearchWorkflowService $workflow): RedirectResponse
    {
        $this->authorizeView($proposal);
        abort_unless(ResearchPermissions::canSubmit(auth()->user(), $proposal), 403);
        abort_if($proposal->file_proposal === null, 422, 'Berkas proposal wajib diunggah.');

        $workflow->submit($proposal);

        return redirect()
            ->route('admin.research.show', $proposal)
            ->with('success', 'Proposal berhasil diajukan.');
    }

    public function download(ResearchProposal $proposal, string $field, ResearchDocumentStorage $storage)
    {
        $this->authorizeView($proposal);
        abort_unless(in_array($field, ['file_proposal', 'file_pengesahan', 'file_pernyataan'], true), 404);

        $path = $proposal->{$field};
        $name = $proposal->{$field.'_name'};

        return $storage->download($path, $name);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(ResearchSiakadLookup $lookup, $user): array
    {
        return [
            'schemes' => ResearchScheme::query()->active()->ordered()->get(),
            'focusAreas' => FocusArea::query()->active()->ordered()->get(),
            'scienceClusters' => ScienceCluster::query()->active()->ordered()->get(),
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
    protected function proposalAttributes(array $data, string $number, $user, ?ResearchProposal $existing = null): array
    {
        $attrs = collect($data)->only([
            'tahun_akademik_id', 'tahun_akademik_nama_snapshot', 'semester_id', 'semester_nama_snapshot',
            'prodi_id', 'prodi_nama_snapshot', 'skema_id', 'judul', 'ketua_dosen_id', 'ketua_dosen_nama_snapshot',
            'bidang_fokus_id', 'rumpun_ilmu_id', 'ringkasan', 'latar_belakang', 'rumusan_masalah',
            'tujuan', 'manfaat', 'metode', 'lokasi', 'jadwal_mulai', 'jadwal_selesai', 'target_luaran',
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

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleUploads($request, ResearchProposal $proposal, ResearchDocumentStorage $storage, array $data): void
    {
        foreach (['file_proposal', 'file_pengesahan', 'file_pernyataan'] as $field) {
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
    protected function syncBudgetItems(ResearchProposal $proposal, array $items): void
    {
        $proposal->budgetItems()->delete();
        foreach ($items as $index => $item) {
            if (empty($item['item_name'])) {
                continue;
            }
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            ResearchBudgetItem::query()->create([
                'research_proposal_id' => $proposal->id,
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

    protected function authorizeView(ResearchProposal $proposal): void
    {
        abort_unless(ResearchPermissions::canView(auth()->user(), $proposal), 403);
    }
}
