<p class="lead">Modul <strong>Data Referensi SIAKAD</strong> menyimpan salinan data akademik dari Siakad-API untuk dipakai di form proposal dan laporan.</p>

<h2>1. Hak akses</h2>
<p>Menu ini tersedia untuk <strong>super admin</strong>, <strong>admin LPPM</strong>, dan <strong>ketua LPPM</strong>.</p>

<h2>2. Data yang disinkronkan</h2>
<ul>
    <li>Program studi (prodi)</li>
    <li>Tahun akademik</li>
    <li>Dosen</li>
    <li>Mahasiswa</li>
    <li>Status mahasiswa</li>
</ul>

<h2>3. Refresh referensi</h2>
<ol>
    <li>Buka menu <strong>Data Referensi SIAKAD</strong>.</li>
    <li>Periksa waktu sinkron terakhir di bilah meta.</li>
    <li>Klik <strong>Refresh</strong> untuk menarik data terbaru dari Siakad-API.</li>
    <li>Tunggu hingga proses selesai; jangan tutup halaman saat masih berjalan.</li>
</ol>
<div class="note">
    Konfigurasi URL dan token Siakad-API ada di <strong>Pengaturan → SIAKAD-API</strong>.
    Token di database meng-override nilai <code>.env</code>.
</div>

<h2>4. Troubleshooting</h2>
<table>
    <thead>
        <tr>
            <th>Gejala</th>
            <th>Tindakan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Refresh gagal / timeout</td>
            <td>Cek koneksi server ke Siakad-API; perbesar timeout di pengaturan</td>
        </tr>
        <tr>
            <td>Prodi/dosen kosong di form</td>
            <td>Jalankan refresh; pastikan data ada di Siakad-API</td>
        </tr>
        <tr>
            <td>Error 401/403</td>
            <td>Token API salah atau kedaluwarsa — perbarui di Pengaturan</td>
        </tr>
    </tbody>
</table>

<h2>5. SOP berkala</h2>
<p>Disarankan refresh referensi <strong>minimal sekali per semester</strong> atau setelah perubahan besar data dosen/mahasiswa di Siakad.</p>
