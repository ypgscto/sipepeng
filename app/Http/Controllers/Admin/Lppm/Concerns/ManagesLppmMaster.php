<?php

namespace App\Http\Controllers\Admin\Lppm\Concerns;

use App\Models\ActivityLog;
use App\Models\Lppm\CommunityServiceScheme;
use App\Models\Lppm\DocumentCategory;
use App\Models\Lppm\DocumentTemplate;
use App\Models\Lppm\FocusArea;
use App\Models\Lppm\FundingSource;
use App\Models\Lppm\LetterType;
use App\Models\Lppm\ResearchScheme;
use App\Models\Lppm\Reviewer;
use App\Models\Lppm\ScienceCluster;
use App\Models\User;
use App\Http\Requests\Lppm\StoreLppmMasterRequest;
use App\Http\Requests\Lppm\UpdateLppmMasterRequest;
use App\Services\ActivityLogger;
use App\Support\Lppm\LppmMasterAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

trait ManagesLppmMaster
{
    abstract protected function entityKey(): string;

    protected function entityConfig(): array
    {
        return config('sipepeng_master.entities.'.$this->entityKey(), []);
    }

    protected function modelClass(): string
    {
        return (string) $this->entityConfig()['model'];
    }

    public function index(Request $request): View
    {
        $config = $this->entityConfig();
        $query = $this->buildIndexQuery($request);

        $records = $query->paginate(25)->withQueryString();

        return view('admin.master.crud.index', [
            'entityKey' => $this->entityKey(),
            'label' => $config['label'],
            'records' => $records,
            'codeColumn' => $config['code_column'] ?? 'code',
            'hasCode' => $config['has_code'] ?? true,
            'filters' => [
                'q' => $request->query('q', ''),
                'status' => $request->query('status', 'active'),
                'trashed' => $request->query('trashed', ''),
            ],
            'canManage' => LppmMasterAccess::canManage(),
        ]);
    }

    public function create(): View
    {
        return view('admin.master.crud.create', $this->formViewData());
    }

    public function store(StoreLppmMasterRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validated();

        $model = $this->persistModel($validated, null, $request);
        $logger->log('created', $model, 'Data master dibuat.', ['attributes' => $model->toArray()]);

        return redirect()
            ->route('admin.master.'.$this->routeName().'.show', $model)
            ->with('success', $this->entityConfig()['label'].' berhasil ditambahkan.');
    }

    public function show(int|string|Model $record): View
    {
        $record = $this->resolveRecord($record);
        $record = $this->loadShowRelations($record);
        $logs = ActivityLog::query()
            ->where('subject_type', $record->getMorphClass())
            ->where('subject_id', $record->getKey())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.master.crud.show', array_merge($this->formViewData(), [
            'record' => $record,
            'logs' => $logs,
        ]));
    }

    public function edit(int|string|Model $record): View
    {
        $record = $this->resolveRecord($record);

        return view('admin.master.crud.edit', array_merge($this->formViewData(), [
            'record' => $record,
        ]));
    }

    public function update(UpdateLppmMasterRequest $request, int|string|Model $record, ActivityLogger $logger): RedirectResponse
    {
        $record = $this->resolveRecord($record);
        $validated = $request->validated();
        $before = $record->toArray();

        $model = $this->persistModel($validated, $record, $request);
        $logger->log('updated', $model, 'Data master diperbarui.', ['before' => $before, 'after' => $model->toArray()]);

        return redirect()
            ->route('admin.master.'.$this->routeName().'.show', $model)
            ->with('success', $this->entityConfig()['label'].' berhasil diperbarui.');
    }

    public function destroy(int|string|Model $record, ActivityLogger $logger): RedirectResponse
    {
        $record = $this->resolveRecord($record);
        $logger->log('deleted', $record, 'Data master dihapus (soft).', ['code' => $record->code ?? $record->template_code ?? $record->id]);
        $record->delete();

        return redirect()
            ->route('admin.master.'.$this->routeName().'.index')
            ->with('success', $this->entityConfig()['label'].' berhasil dihapus.');
    }

    public function toggleActive(int|string|Model $record, ActivityLogger $logger): RedirectResponse
    {
        $record = $this->resolveRecord($record);
        $record->update([
            'is_active' => ! $record->is_active,
            'updated_by' => auth()->id(),
        ]);

        $event = $record->is_active ? 'activated' : 'deactivated';
        $logger->log($event, $record, 'Status aktif diubah.', ['is_active' => $record->is_active]);

        return back()->with('success', 'Status berhasil diubah.');
    }

    public function restore(int $record, ActivityLogger $logger): RedirectResponse
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::withTrashed()->findOrFail($record);
        $record->restore();

        $logger->log('restored', $record, 'Data master dipulihkan.');

        return redirect()
            ->route('admin.master.'.$this->routeName().'.show', $record)
            ->with('success', 'Data berhasil dipulihkan.');
    }

    protected function routeName(): string
    {
        return $this->entityKey();
    }

    protected function resolveRecord(int|string|Model $record): Model
    {
        if ($record instanceof Model) {
            return $record;
        }

        $modelClass = $this->modelClass();

        return $modelClass::query()->withTrashed()->findOrFail($record);
    }

    protected function buildIndexQuery(Request $request): Builder
    {
        $modelClass = $this->modelClass();
        $config = $this->entityConfig();
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'active');
        $trashed = (string) $request->query('trashed', '');

        $query = $modelClass::query();

        if ($trashed === 'only') {
            $query->onlyTrashed();
        } elseif ($trashed === 'with') {
            $query->withTrashed();
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search !== '') {
            if ($this->entityKey() === 'reviewers') {
                $query->whereHas('user', function (Builder $q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('siakad_login', 'like', "%{$search}%");
                });
            } else {
                $query->where(function (Builder $q) use ($search, $config): void {
                    foreach ($config['search'] as $column) {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                });
            }
        }

        foreach ($config['sort'] as $column) {
            $query->orderBy($column);
        }

        if ($this->entityKey() === 'reviewers') {
            $query->with('user');
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function persistModel(array $validated, ?Model $record, Request $request): Model
    {
        $modelClass = $this->modelClass();
        $data = $validated;
        $data['updated_by'] = auth()->id();

        if ($record === null) {
            $data['created_by'] = auth()->id();
            if ($this->entityKey() === 'reviewers') {
                $data['appointed_by'] = auth()->id();
                if (empty($data['appointed_at'])) {
                    $data['appointed_at'] = now()->toDateString();
                }
            }
        }

        if ($this->entityKey() === 'document-templates') {
            return $this->persistDocumentTemplate($data, $record, $request);
        }

        if ($record === null) {
            $record = $modelClass::query()->create($data);
        } else {
            $record->update($data);
        }

        if ($record instanceof ResearchScheme && isset($validated['funding_source_ids'])) {
            $record->fundingSources()->sync($validated['funding_source_ids']);
        }

        if ($record instanceof CommunityServiceScheme && isset($validated['funding_source_ids'])) {
            $record->fundingSources()->sync($validated['funding_source_ids']);
        }

        return $record->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function persistDocumentTemplate(array $data, ?Model $record, Request $request): DocumentTemplate
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('lppm/templates', 'local');
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getClientMimeType() ?? 'application/octet-stream';
            $data['file_size'] = $file->getSize();
        }

        unset($data['file']);

        if ($record === null) {
            return DocumentTemplate::query()->create($data);
        }

        $record->update($data);

        return $record->fresh();
    }

    protected function loadShowRelations(Model $record): Model
    {
        if ($record instanceof Reviewer) {
            $record->load(['user', 'scienceCluster', 'focusArea', 'appointedByUser']);
        }
        if ($record instanceof ResearchScheme || $record instanceof CommunityServiceScheme) {
            $record->load('fundingSources');
        }
        if ($record instanceof DocumentTemplate) {
            $record->load('category');
        }
        if ($record instanceof LetterType) {
            $record->load('documentTemplate');
        }
        if ($record instanceof FocusArea || $record instanceof ScienceCluster) {
            $record->load('parent');
        }

        return $record;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formViewData(): array
    {
        return [
            'entityKey' => $this->entityKey(),
            'label' => $this->entityConfig()['label'],
            'formPartial' => $this->entityConfig()['form'],
            'canManage' => LppmMasterAccess::canManage(),
            'fundingSources' => FundingSource::query()->active()->ordered()->get(),
            'focusAreas' => FocusArea::query()->active()->ordered()->get(),
            'scienceClusters' => ScienceCluster::query()->active()->ordered()->get(),
            'documentCategories' => DocumentCategory::query()->active()->ordered()->get(),
            'documentTemplates' => DocumentTemplate::query()->active()->ordered()->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
