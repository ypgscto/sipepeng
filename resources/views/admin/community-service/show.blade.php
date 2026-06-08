<x-app-layout>
    <x-slot name="header">Detail Proposal PkM</x-slot>

    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header :title="$proposal->judul" :description="$proposal->proposal_number">
            <x-slot name="actions">
                <a href="{{ route('admin.community-service.index') }}" class="sipeng-btn-secondary text-sm">Daftar</a>
                @if ($canEdit)
                    <a href="{{ route('admin.community-service.edit', $proposal) }}" class="sipeng-btn-primary text-sm">Edit</a>
                @endif
                @if ($canSubmit)
                    <form method="POST" action="{{ route('admin.community-service.submit', $proposal) }}" onsubmit="return confirm('Ajukan proposal PkM ini?')">
                        @csrf
                        <button type="submit" class="sipeng-btn-primary text-sm">Ajukan Proposal</button>
                    </form>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    @include('admin.community-service.partials.status-badge', ['proposal' => $proposal])
                    <span class="text-sm text-slate-500">Tahap: {{ $proposal->current_stage }}</span>
                    @if ($proposal->submitted_at)
                        <span class="text-sm text-slate-500">Diajukan: {{ $proposal->submitted_at->format('d M Y H:i') }}</span>
                    @endif
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-slate-500">Tahun Akademik</dt><dd>{{ $proposal->tahun_akademik_nama_snapshot }} <span class="text-xs text-slate-400">({{ $proposal->tahun_akademik_id }})</span></dd></div>
                    <div><dt class="text-slate-500">Semester</dt><dd>{{ $proposal->semester_nama_snapshot }}</dd></div>
                    <div><dt class="text-slate-500">Prodi</dt><dd>{{ $proposal->prodi_nama_snapshot }}</dd></div>
                    <div><dt class="text-slate-500">Skema</dt><dd>{{ $proposal->skema?->name }}</dd></div>
                    <div><dt class="text-slate-500">Ketua</dt><dd>{{ $proposal->ketua_dosen_nama_snapshot }} <span class="text-xs text-slate-400">({{ $proposal->ketua_dosen_id }})</span></dd></div>
                    <div><dt class="text-slate-500">Mitra</dt><dd>{{ $proposal->mitra_nama_snapshot }} <span class="text-xs text-slate-400">({{ $proposal->jenis_mitra_nama_snapshot }})</span></dd></div>
                    <div><dt class="text-slate-500">Total RAB</dt><dd class="font-medium">Rp {{ number_format($proposal->total_rab, 0, ',', '.') }}</dd></div>
                    <div><dt class="text-slate-500">Jadwal</dt><dd>{{ $proposal->jadwal_mulai?->format('d M Y') ?? '—' }} — {{ $proposal->jadwal_selesai?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Lokasi Kegiatan</dt><dd>{{ $proposal->lokasi_kegiatan ?? '—' }}</dd></div>
                </dl>

                @foreach (['masalah_mitra','solusi_ditawarkan','target_capaian','metode_pelaksanaan','target_luaran'] as $field)
                    @if ($proposal->{$field})
                        <div class="mt-4">
                            <h4 class="text-sm font-semibold text-slate-700">{{ str_replace('_', ' ', ucfirst($field)) }}</h4>
                            <p class="mt-1 text-sm text-slate-800 whitespace-pre-line">{{ $proposal->{$field} }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <h3 class="font-semibold text-slate-900 mb-3">RAB</h3>
                @if ($proposal->budgetItems->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada rincian anggaran.</p>
                @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50"><tr>
                            <th class="px-3 py-2 text-left">Komponen</th><th class="px-3 py-2 text-left">Kategori</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                        </tr></thead>
                        <tbody class="divide-y">
                            @foreach ($proposal->budgetItems as $item)
                                <tr><td class="px-3 py-2">{{ $item->item_name }}</td><td class="px-3 py-2">{{ $item->category }}</td>
                                    <td class="px-3 py-2 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <h3 class="font-semibold text-slate-900 mb-3">Dokumen</h3>
                <ul class="space-y-2 text-sm">
                    @foreach (['file_proposal' => 'Proposal', 'file_surat_mitra' => 'Surat Mitra', 'file_pengesahan' => 'Pengesahan'] as $field => $label)
                        <li class="flex items-center justify-between">
                            <span>{{ $label }}</span>
                            @if ($proposal->{$field})
                                <a href="{{ route('admin.community-service.download', [$proposal, $field]) }}" class="text-emerald-700 hover:underline">{{ $proposal->{$field.'_name'} }}</a>
                            @else
                                <span class="text-slate-400">Belum diunggah</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if ($canVerifyAdmin)
            <div class="sipeng-card border-indigo-200">
                <div class="sipeng-card-body">
                    <h3 class="font-semibold text-slate-900 mb-3">Verifikasi Administrasi</h3>
                    <form method="POST" action="{{ route('admin.community-service.admin-verification.store', $proposal) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="sipeng-label">Keputusan</label>
                                <select name="decision" class="sipeng-input" required>
                                    <option value="verified">Terverifikasi</option>
                                    <option value="revision_required">Perlu Revisi</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>
                            <div class="space-y-2 pb-2">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="is_document_complete" value="0">
                                    <input type="checkbox" name="is_document_complete" value="1" class="rounded border-slate-300 text-emerald-600"> Dokumen lengkap
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="is_partner_verified" value="0">
                                    <input type="checkbox" name="is_partner_verified" value="1" class="rounded border-slate-300 text-emerald-600"> Mitra terverifikasi
                                </label>
                            </div>
                        </div>
                        <div><label class="sipeng-label">Catatan</label><textarea name="notes" rows="2" class="sipeng-input"></textarea></div>
                        <button type="submit" class="sipeng-btn-primary text-sm">Simpan Verifikasi</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($canAssignReviewer)
            <div class="sipeng-card">
                <div class="sipeng-card-body">
                    <h3 class="font-semibold text-slate-900 mb-3">Tugaskan Reviewer</h3>
                    <form method="POST" action="{{ route('admin.community-service.review.assign', $proposal) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div class="min-w-[240px] flex-1">
                            <label class="sipeng-label">Reviewer</label>
                            <select name="reviewer_id" class="sipeng-input" required>
                                <option value="">— Pilih —</option>
                                @foreach ($reviewers as $rev)
                                    <option value="{{ $rev->id }}">{{ $rev->user?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="sipeng-btn-primary text-sm">Tugaskan</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($canSubmitReview && $myReview)
            <div class="sipeng-card border-violet-200">
                <div class="sipeng-card-body">
                    <h3 class="font-semibold text-slate-900 mb-3">Form Review</h3>
                    <form method="POST" action="{{ route('admin.community-service.review.submit', $proposal) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="sipeng-label">Rekomendasi</label>
                                <select name="recommendation" class="sipeng-input" required>
                                    <option value="approve">Setujui</option>
                                    <option value="approve_with_revision">Setujui dengan revisi</option>
                                    <option value="reject">Tolak</option>
                                </select>
                            </div>
                            <div>
                                <label class="sipeng-label">Skor (0-100)</label>
                                <input type="number" name="overall_score" min="0" max="100" step="0.01" class="sipeng-input" required>
                            </div>
                        </div>
                        <div><label class="sipeng-label">Ringkasan Review</label><textarea name="summary" rows="3" class="sipeng-input"></textarea></div>
                        <button type="submit" class="sipeng-btn-primary text-sm">Submit Review</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($canDecide)
            <div class="sipeng-card border-emerald-200">
                <div class="sipeng-card-body">
                    <h3 class="font-semibold text-slate-900 mb-3">Penetapan PkM</h3>
                    <form method="POST" action="{{ route('admin.community-service.decision.store', $proposal) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="sipeng-label">Keputusan</label>
                            <select name="decision" class="sipeng-input" required>
                                <option value="approve">Disetujui</option>
                                <option value="reject">Ditolak</option>
                            </select>
                        </div>
                        <div><label class="sipeng-label">Catatan</label><textarea name="notes" rows="2" class="sipeng-input"></textarea></div>
                        <button type="submit" class="sipeng-btn-primary text-sm">Simpan Penetapan</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($proposal->reviews->isNotEmpty())
            <div class="sipeng-card">
                <div class="sipeng-card-body">
                    <h3 class="font-semibold text-slate-900 mb-3">Review</h3>
                    <ul class="divide-y text-sm">
                        @foreach ($proposal->reviews as $review)
                            <li class="py-3">
                                <div class="font-medium">{{ $review->reviewer?->user?->name ?? 'Reviewer' }}</div>
                                <div class="text-slate-600">Status: {{ $review->status }}
                                    @if ($review->overall_score) — Skor: {{ $review->overall_score }} @endif
                                    @if ($review->recommendation) — {{ $review->recommendation }} @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <h3 class="font-semibold text-slate-900 mb-3">Luaran (Publikasi & HKI)</h3>
                <ul class="text-sm space-y-1">
                    @forelse($proposal->publications as $pub)<li><a href="{{ route('admin.publications.show', $pub) }}" class="text-emerald-700 hover:underline">{{ $pub->registration_number }} — {{ Str::limit($pub->judul,40) }}</a></li>@empty @endforelse
                    @forelse($proposal->ipRegistrations as $ip)<li><a href="{{ route('admin.hki.show', $ip) }}" class="text-emerald-700 hover:underline">{{ $ip->registration_number }} — {{ Str::limit($ip->judul,40) }}</a></li>@empty @endforelse
                    @if($proposal->publications->isEmpty() && $proposal->ipRegistrations->isEmpty())<li class="text-slate-500">Belum ada luaran terkait.</li>@endif
                </ul>
                <div class="mt-2 flex gap-2 text-xs">
                    <a href="{{ route('admin.publications.create', ['community_service_proposal_id'=>$proposal->id]) }}" class="text-emerald-700 hover:underline">+ Publikasi</a>
                    <a href="{{ route('admin.hki.create', ['community_service_proposal_id'=>$proposal->id]) }}" class="text-emerald-700 hover:underline">+ HKI</a>
                </div>
            </div>
        </div>

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <h3 class="font-semibold text-slate-900 mb-3">Surat Terkait</h3>
                <ul class="space-y-1 text-sm mb-3">
                    @forelse($proposal->letters as $letter)
                        <li><a href="{{ route('admin.letters.show', $letter) }}" class="text-emerald-700 hover:underline">{{ $letter->displayNumber() }}</a> — @include('admin.letters.partials.status-badge', ['record'=>$letter])</li>
                    @empty
                        <li class="text-slate-500">Belum ada surat.</li>
                    @endforelse
                </ul>
                <div class="flex flex-wrap gap-2 text-xs">
                    <a href="{{ route('admin.letters.create.from-pkm', [$proposal, 'surat_tugas_pkm']) }}" class="text-emerald-700 hover:underline">+ ST PkM</a>
                    <a href="{{ route('admin.letters.create.from-pkm', [$proposal, 'surat_izin_pkm']) }}" class="text-emerald-700 hover:underline">+ Izin PkM</a>
                    <a href="{{ route('admin.letters.create.from-pkm', [$proposal, 'surat_pengantar_mitra']) }}" class="text-emerald-700 hover:underline">+ Pengantar Mitra</a>
                </div>
            </div>
        </div>

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <h3 class="font-semibold text-slate-900 mb-3">Riwayat Status</h3>
                <ul class="divide-y text-sm">
                    @forelse ($proposal->statusHistories as $history)
                        <li class="py-2 flex justify-between gap-4">
                            <span>{{ $history->from_status ?? '—' }} → <strong>{{ $history->to_status }}</strong> ({{ $history->transition }})</span>
                            <time class="text-xs text-slate-500 shrink-0">{{ $history->acted_at?->format('d M Y H:i') }}</time>
                        </li>
                    @empty
                        <li class="text-slate-500">Belum ada riwayat.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <h3 class="font-semibold text-slate-900 mb-3">Log Aktivitas</h3>
                <ul class="divide-y text-sm">
                    @forelse ($logs as $log)
                        <li class="py-2">
                            <span class="font-medium">{{ $log->event }}</span>
                            @if ($log->description) — {{ $log->description }} @endif
                            <time class="block text-xs text-slate-500">{{ $log->created_at?->format('d M Y H:i') }}</time>
                        </li>
                    @empty
                        <li class="text-slate-500">Belum ada log.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
