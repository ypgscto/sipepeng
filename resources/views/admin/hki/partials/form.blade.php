@php
    $record = $record ?? null;
    $inventors = old('inventors', $record?->inventors?->map(fn ($i) => $i->only(['dosen_id','dosen_nama_snapshot','inventor_order','prodi_id','prodi_nama_snapshot']))->all() ?? [[
        'dosen_id' => auth()->user()->siakad_login ?? '', 'dosen_nama_snapshot' => auth()->user()->name,
        'inventor_order' => 1, 'prodi_id' => '', 'prodi_nama_snapshot' => '',
    ]]);
    $sourceType = old('source_type', $record?->source_type ?? ($prefillResearchId ? 'research' : ($prefillPkmId ? 'community_service' : 'standalone')));
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div><label class="sipeng-label">Jenis HKI <span class="text-red-600">*</span></label>
        <select name="ip_type_id" class="sipeng-input" required><option value="">— Pilih —</option>@foreach($ipTypes as $t)<option value="{{ $t->id }}" @selected((string)old('ip_type_id',$record?->ip_type_id)===(string)$t->id)>{{ $t->name }}</option>@endforeach</select></div>
    <div><label class="sipeng-label">Kepemilikan</label><select name="ownership_type" class="sipeng-input"><option value="institution">Institusi</option><option value="inventor">Inventor</option><option value="joint">Bersama</option></select></div>
</div>
<div class="mt-4"><label class="sipeng-label">Judul <span class="text-red-600">*</span></label><input type="text" name="judul" value="{{ old('judul',$record?->judul) }}" class="sipeng-input" required></div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div><label class="sipeng-label">Prodi <span class="text-red-600">*</span></label>
        <select name="prodi_id" id="prodi_id" class="sipeng-input" required onchange="syncProdi(this)"><option value="">— Pilih —</option>@foreach($prodiOptions as $o)<option value="{{ $o['value'] }}" data-label="{{ $o['label'] }}" @selected(old('prodi_id',$record?->prodi_id)===$o['value'])>{{ $o['label'] }}</option>@endforeach</select>
        <input type="hidden" name="prodi_nama_snapshot" id="prodi_nama_snapshot" value="{{ old('prodi_nama_snapshot',$record?->prodi_nama_snapshot) }}"></div>
    <div><label class="sipeng-label">Tanggal Permohonan</label><input type="date" name="application_date" value="{{ old('application_date',$record?->application_date?->format('Y-m-d')) }}" class="sipeng-input"></div>
</div>
<div class="mt-4"><label class="sipeng-label">Sumber</label><select name="source_type" id="source_type" class="sipeng-input" onchange="toggleLink()"><option value="standalone" @selected($sourceType==='standalone')>Mandiri</option><option value="research" @selected($sourceType==='research')>Penelitian</option><option value="community_service" @selected($sourceType==='community_service')>PkM</option></select></div>
<div id="research_link" class="mt-2 {{ $sourceType==='research'?'':'hidden' }}"><select name="research_proposal_id" class="sipeng-input"><option value="">— Pilih proposal —</option>@foreach($researchProposals as $p)<option value="{{ $p->id }}" @selected((string)old('research_proposal_id',$record?->research_proposal_id??$prefillResearchId??'')===(string)$p->id)>{{ $p->proposal_number }}</option>@endforeach</select></div>
<div id="pkm_link" class="mt-2 {{ $sourceType==='community_service'?'':'hidden' }}"><select name="community_service_proposal_id" class="sipeng-input"><option value="">— Pilih proposal —</option>@foreach($pkmProposals as $p)<option value="{{ $p->id }}" @selected((string)old('community_service_proposal_id',$record?->community_service_proposal_id??$prefillPkmId??'')===(string)$p->id)>{{ $p->proposal_number }}</option>@endforeach</select></div>
<div class="mt-4"><label class="sipeng-label">Uraian</label><textarea name="description" rows="3" class="sipeng-input">{{ old('description',$record?->description) }}</textarea></div>
<div class="mt-4"><h3 class="font-semibold mb-2">Inventor</h3>
@foreach($inventors as $idx=>$inv)
<div class="grid grid-cols-2 gap-2 mb-2">
    <select name="inventors[{{ $idx }}][dosen_id]" class="sipeng-input" onchange="syncInv(this,{{ $idx }})"><option value="">— Dosen —</option>@foreach($dosenOptions as $d)<option value="{{ $d['value'] }}" data-nama="{{ $d['nama'] }}" data-prodi-id="{{ $d['prodi_id']??'' }}" data-prodi-nama="{{ $d['prodi_nama']??'' }}" @selected(($inv['dosen_id']??'')===$d['value'])>{{ $d['label'] }}</option>@endforeach</select>
    <input type="hidden" name="inventors[{{ $idx }}][dosen_nama_snapshot]" id="inv_nama_{{ $idx }}" value="{{ $inv['dosen_nama_snapshot']??'' }}">
    <input type="hidden" name="inventors[{{ $idx }}][inventor_order]" value="{{ $idx+1 }}">
    <input type="hidden" name="inventors[{{ $idx }}][prodi_id]" id="inv_prodi_id_{{ $idx }}" value="{{ $inv['prodi_id']??'' }}">
    <input type="text" name="inventors[{{ $idx }}][prodi_nama_snapshot]" id="inv_prodi_nama_{{ $idx }}" value="{{ $inv['prodi_nama_snapshot']??'' }}" class="sipeng-input bg-slate-50" readonly>
</div>@endforeach</div>
<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
@foreach(['file_application'=>'Permohonan (PDF)','file_statement'=>'Pernyataan (PDF)','file_certificate'=>'Sertifikat (PDF)','file_supporting'=>'Pendukung (PDF)'] as $f=>$l)
<div><label class="sipeng-label">{{ $l }} @if(!$record && $f==='file_application')<span class="text-red-600">*</span>@endif</label><input type="file" name="{{ $f }}" accept=".pdf" class="sipeng-input" @if(!$record && $f==='file_application') required @endif>@if($record?->{$f})<p class="text-xs text-slate-500 mt-1">{{ $record->{$f.'_name'} }}</p>@endif</div>@endforeach</div>
@push('scripts')<script>
function syncProdi(el){document.getElementById('prodi_nama_snapshot').value=el.options[el.selectedIndex]?.dataset?.label||'';}
function toggleLink(){const v=document.getElementById('source_type').value;document.getElementById('research_link').classList.toggle('hidden',v!=='research');document.getElementById('pkm_link').classList.toggle('hidden',v!=='community_service');}
function syncInv(el,i){const o=el.options[el.selectedIndex];document.getElementById('inv_nama_'+i).value=o?.dataset?.nama||'';document.getElementById('inv_prodi_id_'+i).value=o?.dataset?.prodiId||'';document.getElementById('inv_prodi_nama_'+i).value=o?.dataset?.prodiNama||'';}
</script>@endpush
