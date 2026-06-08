@php
    $record = $record ?? null;
    $prefill = $prefill ?? [];
    $proposal = $proposal ?? null;
@endphp
@if($proposal || !empty($prefill['research_proposal_id']))
<input type="hidden" name="research_proposal_id" value="{{ old('research_proposal_id', $record?->research_proposal_id ?? $prefill['research_proposal_id'] ?? $proposal?->id) }}">
<input type="hidden" name="proposal_number_snapshot" value="{{ old('proposal_number_snapshot', $record?->proposal_number_snapshot ?? $prefill['proposal_number_snapshot'] ?? $proposal?->proposal_number) }}">
<input type="hidden" name="proposal_judul_snapshot" value="{{ old('proposal_judul_snapshot', $record?->proposal_judul_snapshot ?? $prefill['proposal_judul_snapshot'] ?? $proposal?->judul) }}">
<input type="hidden" name="ketua_dosen_id" value="{{ old('ketua_dosen_id', $record?->ketua_dosen_id ?? $prefill['ketua_dosen_id'] ?? $proposal?->ketua_dosen_id) }}">
<input type="hidden" name="ketua_dosen_nama_snapshot" value="{{ old('ketua_dosen_nama_snapshot', $record?->ketua_dosen_nama_snapshot ?? $prefill['ketua_dosen_nama_snapshot'] ?? $proposal?->ketua_dosen_nama_snapshot) }}">
<input type="hidden" name="prodi_id" value="{{ old('prodi_id', $record?->prodi_id ?? $prefill['prodi_id'] ?? $proposal?->prodi_id) }}">
<input type="hidden" name="prodi_nama_snapshot" value="{{ old('prodi_nama_snapshot', $record?->prodi_nama_snapshot ?? $prefill['prodi_nama_snapshot'] ?? $proposal?->prodi_nama_snapshot) }}">
<div class="sipeng-card bg-slate-50 mb-4"><div class="sipeng-card-body text-sm">
    <p><strong>Proposal:</strong> {{ old('proposal_number_snapshot', $record?->proposal_number_snapshot ?? $proposal?->proposal_number) }}</p>
    <p><strong>Judul:</strong> {{ old('proposal_judul_snapshot', $record?->proposal_judul_snapshot ?? $proposal?->judul) }}</p>
    <p><strong>Ketua:</strong> {{ old('ketua_dosen_nama_snapshot', $record?->ketua_dosen_nama_snapshot ?? $proposal?->ketua_dosen_nama_snapshot) }}</p>
</div></div>
@else
<p class="text-sm text-amber-700 mb-4">Buat aplikasi etik dari halaman detail proposal penelitian.</p>
@endif
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div><label class="sipeng-label">Jenis Studi</label><select name="study_type" class="sipeng-input"><option value="">— Pilih —</option>@foreach(['interventional'=>'Intervensi','observational'=>'Observasional','survey'=>'Survei','qualitative'=>'Kualitatif','other'=>'Lainnya'] as $k=>$l)<option value="{{ $k }}" @selected(old('study_type',$record?->study_type)===$k)>{{ $l }}</option>@endforeach</select></div>
    <div><label class="sipeng-label">Tingkat Risiko</label><select name="risk_level" class="sipeng-input"><option value="">— Pilih —</option>@foreach(['minimal','low','moderate','high'] as $r)<option value="{{ $r }}" @selected(old('risk_level',$record?->risk_level)===$r)>{{ ucfirst($r) }}</option>@endforeach</select></div>
</div>
<div class="mt-4"><label class="sipeng-label">Deskripsi Populasi</label><textarea name="population_description" rows="3" class="sipeng-input">{{ old('population_description',$record?->population_description) }}</textarea></div>
<div class="mt-4"><label class="sipeng-label">Metode Pengumpulan Data</label><textarea name="data_collection_method" rows="2" class="sipeng-input">{{ old('data_collection_method',$record?->data_collection_method) }}</textarea></div>
<div class="mt-4 flex gap-4"><label class="inline-flex gap-2 text-sm"><input type="hidden" name="informed_consent_required" value="0"><input type="checkbox" name="informed_consent_required" value="1" @checked(old('informed_consent_required',$record?->informed_consent_required))> Informed consent</label>
<label class="inline-flex gap-2 text-sm"><input type="hidden" name="conflict_of_interest_declared" value="0"><input type="checkbox" name="conflict_of_interest_declared" value="1" @checked(old('conflict_of_interest_declared',$record?->conflict_of_interest_declared))> Deklarasi COI</label></div>
<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
@foreach(['file_protocol'=>'Protokol (PDF)','file_ethics_application'=>'Formulir Etik (PDF)','file_consent_form'=>'Consent Form (PDF)','file_approval_letter'=>'Surat Persetujuan (PDF)'] as $f=>$l)
<div><label class="sipeng-label">{{ $l }} @if(!$record && in_array($f,['file_protocol','file_ethics_application']))<span class="text-red-600">*</span>@endif</label><input type="file" name="{{ $f }}" accept=".pdf" class="sipeng-input" @if(!$record && in_array($f,['file_protocol','file_ethics_application'])) required @endif>@if($record?->{$f})<p class="text-xs text-slate-500">{{ $record->{$f.'_name'} }}</p>@endif</div>@endforeach</div>
