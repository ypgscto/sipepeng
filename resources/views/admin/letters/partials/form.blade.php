@php $v = isset($record) ? $record : null; $old = $prefill ?? []; @endphp
<div class="space-y-4" x-data="{ typeId: '{{ old('letter_type_id', $v?->letter_type_id ?? $old['letter_type_id'] ?? '') }}' }">
    <div><label class="sipeng-label">Jenis Surat *</label>
        <select name="letter_type_id" class="sipeng-input" required x-model="typeId">
            <option value="">— Pilih —</option>
            @foreach($letterTypes as $t)<option value="{{ $t->id }}" @selected(old('letter_type_id', $v?->letter_type_id ?? $old['letter_type_id'] ?? '')==$t->id) data-code="{{ $t->code }}" data-applies="{{ $t->applies_to }}">{{ $t->name }}</option>@endforeach
        </select>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="sipeng-label">Perihal *</label><input type="text" name="perihal" value="{{ old('perihal', $v?->perihal ?? $old['perihal'] ?? '') }}" class="sipeng-input" required maxlength="255"></div>
        <div><label class="sipeng-label">Tanggal Surat *</label><input type="date" name="letter_date" value="{{ old('letter_date', optional($v?->letter_date)->format('Y-m-d') ?? $old['letter_date'] ?? now()->toDateString()) }}" class="sipeng-input" required></div>
        <div><label class="sipeng-label">Tempat</label><input type="text" name="place_of_issue" value="{{ old('place_of_issue', $v?->place_of_issue ?? $old['place_of_issue'] ?? config('sipepeng_letters.place_of_issue')) }}" class="sipeng-input"></div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div x-show="['research','surat_tugas_penelitian','surat_izin_penelitian','surat_keterangan_selesai_penelitian'].includes(document.querySelector('[name=letter_type_id] option:checked')?.dataset?.applies) || document.querySelector('[name=letter_type_id] option:checked')?.dataset?.applies === 'research'">
            <label class="sipeng-label">Proposal Penelitian</label>
            <select name="research_proposal_id" class="sipeng-input">
                <option value="">— Opsional —</option>
                @foreach($researchProposals as $p)<option value="{{ $p->id }}" @selected(old('research_proposal_id', $v?->research_proposal_id ?? $old['research_proposal_id'] ?? '')==$p->id)>{{ $p->proposal_number }} — {{ Str::limit($p->judul, 50) }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="sipeng-label">Proposal PkM</label>
            <select name="community_service_proposal_id" class="sipeng-input">
                <option value="">— Opsional —</option>
                @foreach($pkmProposals as $p)<option value="{{ $p->id }}" @selected(old('community_service_proposal_id', $v?->community_service_proposal_id ?? $old['community_service_proposal_id'] ?? '')==$p->id)>{{ $p->proposal_number }} — {{ Str::limit($p->judul, 50) }}</option>@endforeach
            </select>
        </div>
        <div><label class="sipeng-label">Mitra</label>
            <select name="partner_id" class="sipeng-input"><option value="">— Opsional —</option>@foreach($partners as $p)<option value="{{ $p->id }}" @selected(old('partner_id', $v?->partner_id ?? $old['partner_id'] ?? '')==$p->id)>{{ $p->name }}</option>@endforeach</select>
        </div>
        <div><label class="sipeng-label">Reviewer</label>
            <select name="reviewer_id" class="sipeng-input"><option value="">— Opsional —</option>@foreach($reviewers as $r)<option value="{{ $r->id }}" @selected(old('reviewer_id', $v?->reviewer_id ?? $old['reviewer_id'] ?? '')==$r->id)>{{ $r->user?->name }}</option>@endforeach</select>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 border-t pt-4">
        <div><label class="sipeng-label">Penerima (Nama)</label><input type="text" name="recipient_external_name" value="{{ old('recipient_external_name', $v?->recipient_external_name ?? '') }}" class="sipeng-input"></div>
        <div><label class="sipeng-label">Instansi Penerima</label><input type="text" name="recipient_external_institution" value="{{ old('recipient_external_institution', $v?->recipient_external_institution ?? '') }}" class="sipeng-input"></div>
        <div class="sm:col-span-2"><label class="sipeng-label">Alamat Penerima</label><textarea name="recipient_external_address" rows="2" class="sipeng-input">{{ old('recipient_external_address', $v?->recipient_external_address ?? '') }}</textarea></div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 border-t pt-4">
        <div><label class="sipeng-label">Tanggal Acara</label><input type="date" name="event_date" value="{{ old('event_date', optional($v?->event_date)->format('Y-m-d') ?? '') }}" class="sipeng-input"></div>
        <div><label class="sipeng-label">Waktu Acara</label><input type="text" name="event_time" value="{{ old('event_time', $v?->event_time ?? '') }}" class="sipeng-input" placeholder="09.00 WITA"></div>
        <div class="sm:col-span-2"><label class="sipeng-label">Tempat Acara</label><input type="text" name="event_location" value="{{ old('event_location', $v?->event_location ?? '') }}" class="sipeng-input"></div>
        <div class="sm:col-span-2"><label class="sipeng-label">Agenda Acara</label><textarea name="event_agenda" rows="3" class="sipeng-input">{{ old('event_agenda', $v?->event_agenda ?? '') }}</textarea></div>
    </div>

    <div><label class="sipeng-label">Isi Surat (opsional, override template)</label><textarea name="body_content" rows="6" class="sipeng-input" placeholder="Kosongkan untuk menggunakan teks baku template.">{{ old('body_content', $v?->body_content ?? '') }}</textarea></div>
    <div><label class="sipeng-label">Catatan Internal</label><textarea name="notes_internal" rows="2" class="sipeng-input">{{ old('notes_internal', $v?->notes_internal ?? '') }}</textarea></div>
</div>
