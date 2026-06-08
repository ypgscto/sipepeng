<p class="lead">Modul <strong>Pengaturan</strong> untuk admin LPPM dan super admin: branding, integrasi Siakad, peran, template, dan backup.</p>

<h2>1. Hak akses</h2>
<p>Super admin dan admin LPPM. Beberapa fitur (backup) hanya <strong>super admin</strong>.</p>

<h2>2. Profil & branding</h2>
<ul>
    <li>Nama aplikasi, subtitle, nama institusi</li>
    <li>Teks footer (default: YPGS IT Division)</li>
    <li>Upload logo institusi (PNG/JPG/WebP)</li>
</ul>

<h2>3. SIAKAD-API</h2>
<ol>
    <li>Buka <strong>Pengaturan → SIAKAD-API</strong>.</li>
    <li>Isi URL base Siakad-API dan token server-to-server.</li>
    <li>Simpan — token disimpan terenkripsi di database.</li>
    <li>Uji dengan refresh referensi SIAKAD.</li>
</ol>
<div class="warn">
    Jangan membagikan token API. Nilai di database meng-override <code>.env</code> setelah disimpan.
</div>

<h2>4. Mapping peran</h2>
<p>Petakan <code>jenis_user</code> / level Siakad ke peran SiPepeng (admin LPPM, dosen, ketua prodi, dll.). Override khusus super admin IT dapat dikonfigurasi di file config bootstrap.</p>

<h2>5. Template & dokumen</h2>
<p>Kelola tautan unduhan template formulir LPPM yang ditampilkan ke pengguna.</p>

<h2>6. Backup database (super admin)</h2>
<ol>
    <li>Menu <strong>Pengaturan → Backup</strong>.</li>
    <li>Buat backup manual atau unduh backup terakhir.</li>
    <li>Simpan file backup di lokasi aman di luar server web.</li>
</ol>

<h2>7. Aktivasi pengguna</h2>
<p>Pengguna baru dari login SIAKAD tercatat di database tetapi login SiPepeng diblokir sampai admin mengaktifkan <code>is_allowed_login</code> dan menetapkan peran.</p>

<h2>8. SOP keamanan production</h2>
<ul>
    <li><code>APP_DEBUG=false</code></li>
    <li>HTTPS + <code>SESSION_SECURE_COOKIE=true</code></li>
    <li>Backup database terjadwal</li>
    <li>Gunakan password kuat untuk akun database</li>
</ul>
