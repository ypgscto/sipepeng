@include('admin.master.forms._base-fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="registration_body" class="sipeng-label">Lembaga Pendaftaran</label>
        <input type="text" id="registration_body" name="registration_body" value="{{ old('registration_body', $record?->registration_body) }}" class="sipeng-input">
    </div>
    <div>
        <label for="typical_duration_months" class="sipeng-label">Durasi Tipikal (bulan)</label>
        <input type="number" id="typical_duration_months" name="typical_duration_months" min="1" max="120"
            value="{{ old('typical_duration_months', $record?->typical_duration_months) }}" class="sipeng-input">
    </div>
</div>
