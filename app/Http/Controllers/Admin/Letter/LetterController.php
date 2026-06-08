<?php

namespace App\Http\Controllers\Admin\Letter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Letter\StoreLetterRequest;
use App\Http\Requests\Letter\SubmitLetterRequest;
use App\Http\Requests\Letter\UpdateLetterRequest;
use App\Http\Requests\Letter\UploadSignedLetterRequest;
use App\Models\ActivityLog;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Letter\Letter;
use App\Models\Letter\LetterRecipient;
use App\Models\Lppm\LetterType;
use App\Models\Lppm\Partner;
use App\Models\Lppm\Reviewer;
use App\Models\Research\ResearchProposal;
use App\Services\ActivityLogger;
use App\Services\Letter\LetterDocumentStorage;
use App\Services\Letter\LetterPdfService;
use App\Services\Letter\LetterWorkflowService;
use App\Services\Lppm\LppmSiakadLookup;
use App\Support\Letter\LetterLinkHelper;
use App\Support\Letter\LetterPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LetterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Letter::query()
            ->visibleTo($user)
            ->with(['letterType', 'creator'])
            ->orderByDesc('updated_at');

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($builder) use ($q): void {
                $builder->where('perihal', 'like', "%{$q}%")
                    ->orWhere('internal_number', 'like', "%{$q}%")
                    ->orWhere('letter_number', 'like', "%{$q}%")
                    ->orWhere('proposal_judul_snapshot', 'like', "%{$q}%");
            });
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($typeId = $request->integer('letter_type_id')) {
            $query->where('letter_type_id', $typeId);
        }
        if ($tahun = $request->integer('tahun')) {
            $query->whereYear('letter_date', $tahun);
        }

        return view('admin.letters.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'letter_type_id', 'tahun']),
            'statusOptions' => config('sipepeng_letters.statuses', []),
            'letterTypes' => LetterType::query()->active()->ordered()->get(),
            'canCreate' => LetterPermissions::canCreate($user),
        ]);
    }

    public function create(LppmSiakadLookup $lookup, Request $request): View
    {
        abort_unless(LetterPermissions::canCreate($request->user()), 403);

        return view('admin.letters.create', $this->formData($lookup, $request));
    }

    public function createFromResearch(ResearchProposal $proposal, string $type, Request $request): View
    {
        abort_unless(LetterPermissions::canCreate($request->user()), 403);
        $letterType = LetterType::query()->where('code', $type)->firstOrFail();

        return view('admin.letters.create', array_merge(
            $this->formData(app(LppmSiakadLookup::class), $request),
            [
                'prefill' => array_merge(
                    LetterLinkHelper::prefillFromResearch($proposal),
                    [
                        'letter_type_id' => $letterType->id,
                        'perihal' => LetterLinkHelper::defaultPerihal($letterType, $proposal->judul),
                        'letter_date' => now()->toDateString(),
                    ],
                ),
            ],
        ));
    }

    public function createFromPkm(CommunityServiceProposal $proposal, string $type, Request $request): View
    {
        abort_unless(LetterPermissions::canCreate($request->user()), 403);
        $letterType = LetterType::query()->where('code', $type)->firstOrFail();

        return view('admin.letters.create', array_merge(
            $this->formData(app(LppmSiakadLookup::class), $request),
            [
                'prefill' => array_merge(
                    LetterLinkHelper::prefillFromPkm($proposal),
                    [
                        'letter_type_id' => $letterType->id,
                        'perihal' => LetterLinkHelper::defaultPerihal($letterType, $proposal->judul),
                        'letter_date' => now()->toDateString(),
                    ],
                ),
            ],
        ));
    }

    public function store(StoreLetterRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $data = LetterLinkHelper::resolveSnapshots($request->validated());
        $type = LetterType::query()->findOrFail($data['letter_type_id']);

        $letter = Letter::query()->create(array_merge(
            collect($data)->except(['recipients'])->all(),
            [
                'internal_number' => LetterWorkflowService::generateInternalNumber(),
                'letter_prefix_snapshot' => $type->letter_prefix,
                'document_template_id' => $type->document_template_id,
                'place_of_issue' => $data['place_of_issue'] ?? config('sipepeng_letters.place_of_issue'),
                'status' => 'draft',
                'current_stage' => 'submission',
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ],
        ));

        $this->syncRecipients($letter, $data['recipients'] ?? []);
        $logger->log('created', $letter, 'Surat dibuat.', ['internal_number' => $letter->internal_number], logName: 'lppm_letter');

        return redirect()->route('admin.letters.show', $letter)->with('success', 'Surat berhasil dibuat.');
    }

    public function show(Letter $letter): View
    {
        abort_unless(LetterPermissions::canView(auth()->user(), $letter), 403);
        $letter->load([
            'letterType', 'documentTemplate', 'researchProposal', 'communityServiceProposal',
            'partner', 'reviewer.user', 'recipients', 'statusHistories.actor', 'approvals.approver', 'creator',
        ]);

        $logs = ActivityLog::query()->where('log_name', 'lppm_letter')
            ->where('subject_type', $letter->getMorphClass())->where('subject_id', $letter->id)
            ->orderByDesc('created_at')->limit(15)->get();

        $user = auth()->user();

        return view('admin.letters.show', [
            'record' => $letter,
            'logs' => $logs,
            'canEdit' => LetterPermissions::canEdit($user, $letter),
            'canSubmit' => LetterPermissions::canSubmit($user, $letter),
            'canApprove' => LetterPermissions::canApprove($user) && $letter->status === 'pending_approval',
            'canIssue' => LetterPermissions::canIssue($user, $letter),
            'canUploadSigned' => LetterPermissions::canUploadSigned($user, $letter),
        ]);
    }

    public function edit(Letter $letter, LppmSiakadLookup $lookup, Request $request): View
    {
        abort_unless(LetterPermissions::canEdit(auth()->user(), $letter), 403);
        $letter->load('recipients');

        return view('admin.letters.edit', array_merge($this->formData($lookup, $request), ['record' => $letter]));
    }

    public function update(UpdateLetterRequest $request, Letter $letter, ActivityLogger $logger): RedirectResponse
    {
        $data = LetterLinkHelper::resolveSnapshots($request->validated());
        $before = $letter->toArray();
        $type = LetterType::query()->findOrFail($data['letter_type_id']);

        $letter->update(array_merge(
            collect($data)->except(['recipients'])->all(),
            [
                'letter_prefix_snapshot' => $type->letter_prefix,
                'document_template_id' => $type->document_template_id,
                'updated_by' => $request->user()->id,
            ],
        ));

        $this->syncRecipients($letter, $data['recipients'] ?? []);
        $logger->log('updated', $letter, 'Surat diperbarui.', ['before' => $before], logName: 'lppm_letter');

        return redirect()->route('admin.letters.show', $letter)->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(Letter $letter): RedirectResponse
    {
        abort_unless(LetterPermissions::canEdit(auth()->user(), $letter), 403);
        abort_unless(in_array($letter->status, ['draft', 'revision_required'], true), 403);
        $letter->delete();

        return redirect()->route('admin.letters.index')->with('success', 'Draft surat dihapus.');
    }

    public function submit(SubmitLetterRequest $request, Letter $letter, LetterWorkflowService $workflow): RedirectResponse
    {
        $workflow->submit($letter);

        return redirect()->route('admin.letters.show', $letter)->with('success', 'Surat berhasil diajukan.');
    }

    public function previewPdf(Letter $letter, LetterPdfService $pdfService)
    {
        abort_unless(LetterPermissions::canView(auth()->user(), $letter), 403);

        return $pdfService->stream($letter, watermark: ! $letter->isIssued());
    }

    public function downloadPdf(Letter $letter, LetterDocumentStorage $storage)
    {
        abort_unless(LetterPermissions::canView(auth()->user(), $letter), 403);
        abort_unless($letter->file_pdf, 404);

        $path = $storage->downloadPath($letter, 'file_pdf');
        abort_unless($path, 404);

        $disk = config('sipepeng_letters.storage_disk', 'local');

        return Storage::disk($disk)->download($path, $letter->file_pdf_name ?? 'surat.pdf');
    }

    public function downloadSigned(Letter $letter, LetterDocumentStorage $storage)
    {
        abort_unless(LetterPermissions::canView(auth()->user(), $letter), 403);
        abort_unless($letter->file_signed_scan, 404);

        $path = $storage->downloadPath($letter, 'file_signed_scan');
        abort_unless($path, 404);

        $disk = config('sipepeng_letters.storage_disk', 'local');

        return Storage::disk($disk)->download($path, $letter->file_signed_scan_name ?? 'surat-ttd.pdf');
    }

    public function uploadSigned(UploadSignedLetterRequest $request, Letter $letter, LetterDocumentStorage $storage, ActivityLogger $logger): RedirectResponse
    {
        $storage->storeSignedScan($letter, $request->file('file_signed_scan'));
        $logger->log('signed_scan_uploaded', $letter, 'Scan surat bertanda tangan diunggah.', logName: 'lppm_letter');

        return redirect()->route('admin.letters.show', $letter)->with('success', 'Scan surat final berhasil diunggah.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(LppmSiakadLookup $lookup, Request $request): array
    {
        $user = $request->user();
        $letterTypes = LetterType::query()->active()->ordered()->get()
            ->filter(fn (LetterType $t) => LetterPermissions::canCreateType($user, $t->code));

        return [
            'letterTypes' => $letterTypes,
            'researchProposals' => \App\Support\Lppm\ProposalLinkHelper::linkableResearchQuery($user)->limit(100)->get(),
            'pkmProposals' => \App\Support\Lppm\ProposalLinkHelper::linkablePkmQuery($user)->limit(100)->get(),
            'partners' => Partner::query()->active()->orderBy('name')->get(),
            'reviewers' => Reviewer::query()->where('is_active', true)->with('user')->get(),
            'prefill' => $request->old() ?: [],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $recipients
     */
    protected function syncRecipients(Letter $letter, array $recipients): void
    {
        $letter->recipients()->delete();
        foreach (array_values($recipients) as $index => $row) {
            if (empty($row['name'])) {
                continue;
            }
            LetterRecipient::query()->create([
                'letter_id' => $letter->id,
                'recipient_type' => $row['recipient_type'] ?? 'external',
                'name' => $row['name'],
                'email' => $row['email'] ?? null,
                'institution' => $row['institution'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
