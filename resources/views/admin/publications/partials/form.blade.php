@php
    $record = $record ?? null;
    $authors = old('authors', $record?->authors?->map(fn ($a) => $a->only(['dosen_id','dosen_nama_snapshot','author_order','role','prodi_id','prodi_nama_snapshot']))->all() ?? [[
        'dosen_id' => auth()->user()->siakad_login ?? '',
        'dosen_nama_snapshot' => auth()->user()->name,
        'author_order' => 1, 'role' => 'lead', 'prodi_id' => '', 'prodi_nama_snapshot' => '',
    ]]);
    if (count($authors) < 2) {
        $authors = array_pad($authors, 2, ['dosen_id'=>'','dosen_nama_snapshot'=>'','author_order'=>2,'role'=>'co_author','prodi_id'=>'','prodi_nama_snapshot'=>'']);
    }
    $sourceType = old('source_type', $record?->source_type ?? ($prefillResearchId ? 'research' : ($prefillPkmId ? 'community_service' : 'standalone')));
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="sipeng-label">Jenis Publikasi <span class="text-red-600">*</span></label>
        <select name="publication_type_id" class="sipeng-input" required>
            <option value="">— Pilih —</option>
            @foreach ($publicationTypes as $type)
                <option value="{{ $type->id }}" @selected((string) old('publication_type_id', $record?->publication_type_id) === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="sipeng-label">Tahun Terbit</label>
        <input type="number" name="publication_year" value="{{ old('publication_year', $record?->publication_year ?? now()->year) }}" class="sipeng-input" min="1990" max="2100">
    </div>
</div>

<div class="mt-4">
    <label class="sipeng-label">Judul <span class="text-red-600">*</span></label>
    <input type="text" name="judul" value="{{ old('judul', $record?->judul) }}" class="sipeng-input" required>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="sipeng-label">Prodi (SIAKAD) <span class="text-red-600">*</span></label>
        <select name="prodi_id" id="prodi_id" class="sipeng-input" required onchange="syncProdiLabel(this)">
            <option value="">— Pilih —</option>
            @foreach ($prodiOptions as $opt)
                <option value="{{ $opt['value'] }}" data-label="{{ $opt['label'] }}" @selected(old('prodi_id', $record?->prodi_id) === $opt['value'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
        <input type="hidden" name="prodi_nama_snapshot" id="prodi_nama_snapshot" value="{{ old('prodi_nama_snapshot', $record?->prodi_nama_snapshot) }}">
    </div>
    <div>
        <label class="sipeng-label">Jurnal / Penerbit</label>
        <input type="text" name="journal_or_publisher" value="{{ old('journal_or_publisher', $record?->journal_or_publisher) }}" class="sipeng-input">
    </div>
</div>

<div class="mt-4">
    <label class="sipeng-label">Sumber / Keterkaitan</label>
    <select name="source_type" id="source_type" class="sipeng-input" onchange="toggleProposalLink()">
        <option value="standalone" @selected($sourceType === 'standalone')>Mandiri (standalone)</option>
        <option value="research" @selected($sourceType === 'research')>Terhubung Penelitian</option>
        <option value="community_service" @selected($sourceType === 'community_service')>Terhubung PkM</option>
    </select>
</div>

<div id="research_link" class="mt-2 {{ $sourceType === 'research' ? '' : 'hidden' }}">
    <label class="sipeng-label">Proposal Penelitian</label>
    <select name="research_proposal_id" class="sipeng-input">
        <option value="">— Pilih —</option>
        @foreach ($researchProposals as $p)
            <option value="{{ $p->id }}" @selected((string) old('research_proposal_id', $record?->research_proposal_id ?? $prefillResearchId ?? '') === (string) $p->id)>{{ $p->proposal_number }} — {{ Str::limit($p->judul, 60) }}</option>
        @endforeach
    </select>
</div>
<div id="pkm_link" class="mt-2 {{ $sourceType === 'community_service' ? '' : 'hidden' }}">
    <label class="sipeng-label">Proposal PkM</label>
    <select name="community_service_proposal_id" class="sipeng-input">
        <option value="">— Pilih —</option>
        @foreach ($pkmProposals as $p)
            <option value="{{ $p->id }}" @selected((string) old('community_service_proposal_id', $record?->community_service_proposal_id ?? $prefillPkmId ?? '') === (string) $p->id)>{{ $p->proposal_number }} — {{ Str::limit($p->judul, 60) }}</option>
        @endforeach
    </select>
</div>

<div class="mt-4">
    <h3 class="font-semibold text-slate-900 mb-2">Penulis (dosen SIAKAD)</h3>
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50"><tr><th class="px-2 py-2 text-left">Dosen</th><th class="px-2 py-2">Peran</th><th class="px-2 py-2">Prodi</th></tr></thead>
        <tbody>
            @foreach ($authors as $idx => $author)
                <tr>
                    <td class="px-2 py-1">
                        <select name="authors[{{ $idx }}][dosen_id]" class="sipeng-input author-select" data-idx="{{ $idx }}" onchange="syncAuthor(this)">
                            <option value="">— Pilih —</option>
                            @foreach ($dosenOptions as $d)
                                <option value="{{ $d['value'] }}" data-nama="{{ $d['nama'] }}" data-prodi-id="{{ $d['prodi_id'] ?? '' }}" data-prodi-nama="{{ $d['prodi_nama'] ?? '' }}"
                                    @selected(($author['dosen_id'] ?? '') === $d['value'])>{{ $d['label'] }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="authors[{{ $idx }}][dosen_nama_snapshot]" id="author_nama_{{ $idx }}" value="{{ $author['dosen_nama_snapshot'] ?? '' }}">
                        <input type="hidden" name="authors[{{ $idx }}][author_order]" value="{{ $author['author_order'] ?? $idx + 1 }}">
                    </td>
                    <td class="px-2 py-1">
                        <select name="authors[{{ $idx }}][role]" class="sipeng-input">
                            @foreach (['lead'=>'Ketua','corresponding'=>'Korespondensi','co_author'=>'Co-author'] as $k=>$l)
                                <option value="{{ $k }}" @selected(($author['role'] ?? 'co_author') === $k)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-2 py-1">
                        <input type="hidden" name="authors[{{ $idx }}][prodi_id]" id="author_prodi_id_{{ $idx }}" value="{{ $author['prodi_id'] ?? '' }}">
                        <input type="text" name="authors[{{ $idx }}][prodi_nama_snapshot]" id="author_prodi_nama_{{ $idx }}" value="{{ $author['prodi_nama_snapshot'] ?? '' }}" class="sipeng-input bg-slate-50" readonly>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div><label class="sipeng-label">DOI</label><input type="text" name="doi" value="{{ old('doi', $record?->doi) }}" class="sipeng-input"></div>
    <div><label class="sipeng-label">ISSN</label><input type="text" name="issn" value="{{ old('issn', $record?->issn) }}" class="sipeng-input"></div>
    <div><label class="sipeng-label">Indeks</label><input type="text" name="indexing_label" value="{{ old('indexing_label', $record?->indexing_label) }}" class="sipeng-input" placeholder="SINTA, Scopus, dll."></div>
    <div><label class="sipeng-label">URL</label><input type="url" name="url" value="{{ old('url', $record?->url) }}" class="sipeng-input"></div>
</div>

<div class="mt-4">
    <label class="sipeng-label">Abstrak</label>
    <textarea name="abstract" rows="3" class="sipeng-input">{{ old('abstract', $record?->abstract) }}</textarea>
</div>

<div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach (['file_manuscript' => 'Manuskrip (PDF)', 'file_acceptance_letter' => 'Surat Accept (PDF)', 'file_published' => 'Artikel Terbit (PDF)', 'file_other' => 'Lainnya (PDF)'] as $field => $label)
        <div>
            <label class="sipeng-label">{{ $label }} @if (! $record && $field === 'file_manuscript')<span class="text-red-600">*</span>@endif</label>
            <input type="file" name="{{ $field }}" accept=".pdf" class="sipeng-input" @if (! $record && $field === 'file_manuscript') required @endif>
            @if ($record?->{$field})<p class="mt-1 text-xs text-slate-500">{{ $record->{$field.'_name'} }}</p>@endif
        </div>
    @endforeach
</div>

@push('scripts')
<script>
function syncProdiLabel(el){const o=el.options[el.selectedIndex];document.getElementById('prodi_nama_snapshot').value=o?.dataset?.label||'';}
function toggleProposalLink(){
    const v=document.getElementById('source_type').value;
    document.getElementById('research_link').classList.toggle('hidden',v!=='research');
    document.getElementById('pkm_link').classList.toggle('hidden',v!=='community_service');
}
function syncAuthor(el){
    const o=el.options[el.selectedIndex], i=el.dataset.idx;
    document.getElementById('author_nama_'+i).value=o?.dataset?.nama||'';
    document.getElementById('author_prodi_id_'+i).value=o?.dataset?.prodiId||'';
    document.getElementById('author_prodi_nama_'+i).value=o?.dataset?.prodiNama||'';
}
document.addEventListener('DOMContentLoaded',()=>{
    const p=document.getElementById('prodi_id'); if(p?.value) p.dispatchEvent(new Event('change'));
    document.querySelectorAll('.author-select').forEach(el=>{if(el.value) el.dispatchEvent(new Event('change'));});
});
</script>
@endpush
