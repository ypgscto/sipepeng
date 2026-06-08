<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_proposal_team_members', function (Blueprint $table): void {
            $table->id();
            $table->string('activity_type', 20);
            $table->unsignedBigInteger('research_proposal_id')->nullable();
            $table->unsignedBigInteger('community_service_proposal_id')->nullable();
            $table->string('member_type', 20)->default('mahasiswa');
            $table->string('mahasiswa_id', 50)->nullable();
            $table->string('mahasiswa_nama_snapshot', 150)->nullable();
            $table->string('dosen_id', 50)->nullable();
            $table->string('dosen_nama_snapshot', 150)->nullable();
            $table->string('prodi_id', 50)->nullable();
            $table->string('prodi_nama_snapshot', 150)->nullable();
            $table->string('role_label', 50)->default('anggota');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('research_proposal_id', 'lppm_ptm_research_fk')
                ->references('id')->on('lppm_research_proposals')->cascadeOnDelete();
            $table->foreign('community_service_proposal_id', 'lppm_ptm_pkm_fk')
                ->references('id')->on('lppm_community_service_proposals')->cascadeOnDelete();

            $table->index(['activity_type', 'mahasiswa_id']);
            $table->index('prodi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_proposal_team_members');
    }
};
