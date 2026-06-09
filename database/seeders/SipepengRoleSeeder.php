<?php

namespace Database\Seeders;

use App\Models\SipepengRole;
use Illuminate\Database\Seeder;

class SipepengRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'code' => 'super_admin',
                'name' => 'Super Admin',
                'description' => 'Akses penuh sistem SiPepeng.',
                'sort_order' => 10,
            ],
            [
                'code' => 'ketua_lppm',
                'name' => 'Ketua LPPM',
                'description' => 'Pimpinan LPPM, dashboard dan laporan institusi.',
                'siakad_map_type' => 'level_id',
                'siakad_map_key' => '91',
                'sort_order' => 20,
            ],
            [
                'code' => 'admin_lppm',
                'name' => 'Admin LPPM',
                'description' => 'Operator LPPM, kelola proposal, review, dan laporan.',
                'siakad_map_type' => 'jenis_user',
                'siakad_map_key' => '8',
                'sort_order' => 30,
            ],
            [
                'code' => 'reviewer',
                'name' => 'Reviewer',
                'description' => 'Penilai proposal penelitian dan pengabdian.',
                'sort_order' => 40,
            ],
            [
                'code' => 'dosen',
                'name' => 'Dosen',
                'description' => 'Pengusul penelitian dan pengabdian masyarakat.',
                'siakad_map_type' => 'jenis_user',
                'siakad_map_key' => '7',
                'sort_order' => 50,
            ],
            [
                'code' => 'ketua_prodi',
                'name' => 'Ketua Prodi',
                'description' => 'Monitoring kinerja penelitian dan PkM per program studi.',
                'siakad_map_type' => 'jenis_user',
                'siakad_map_key' => '6',
                'sort_order' => 60,
            ],
            [
                'code' => 'pimpinan',
                'name' => 'Pimpinan',
                'description' => 'Pimpinan institusi / Kepala Lembaga, akses dashboard rekap.',
                'siakad_map_type' => 'jenis_user',
                'siakad_map_key' => '5',
                'sort_order' => 70,
            ],
            [
                'code' => 'mahasiswa',
                'name' => 'Mahasiswa',
                'description' => 'Peserta pengabdian atau tim mahasiswa (jika diperlukan).',
                'siakad_map_type' => 'level_id',
                'siakad_map_key' => '100',
                'sort_order' => 80,
            ],
        ];

        foreach ($roles as $role) {
            SipepengRole::query()->updateOrCreate(
                ['code' => $role['code']],
                array_merge($role, ['is_active' => true]),
            );
        }
    }
}
