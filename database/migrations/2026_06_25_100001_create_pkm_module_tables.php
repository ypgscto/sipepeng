<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_partners', function (Blueprint $table): void {
            $table->id();
            $table->string('partner_code', 30)->unique();
            $table->string('name', 150);
            $table->unsignedBigInteger('partner_type_id');
            $table->foreign('partner_type_id', 'lppm_partners_type_fk')
                ->references('id')->on('lppm_partner_types')->restrictOnDelete();
            $table->string('legal_name', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_community_service_proposals', function (Blueprint $table): void {
            $table->id();
            $table->string('proposal_number', 40)->unique();
            $table->string('tahun_akademik_id', 50);
            $table->string('tahun_akademik_nama_snapshot', 100);
            $table->string('semester_id', 50);
            $table->string('semester_nama_snapshot', 100);
            $table->string('prodi_id', 50);
            $table->string('prodi_nama_snapshot', 150);
            $table->unsignedBigInteger('skema_id');
            $table->foreign('skema_id', 'lppm_csp_skema_fk')
                ->references('id')->on('lppm_community_service_schemes')->restrictOnDelete();
            $table->string('judul', 255);
            $table->string('ketua_dosen_id', 50);
            $table->string('ketua_dosen_nama_snapshot', 150);
            $table->foreignId('ketua_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('mitra_id');
            $table->foreign('mitra_id', 'lppm_csp_mitra_fk')
                ->references('id')->on('lppm_partners')->restrictOnDelete();
            $table->string('mitra_nama_snapshot', 150);
            $table->unsignedBigInteger('jenis_mitra_id');
            $table->foreign('jenis_mitra_id', 'lppm_csp_jenis_mitra_fk')
                ->references('id')->on('lppm_partner_types')->restrictOnDelete();
            $table->string('jenis_mitra_nama_snapshot', 150);
            $table->text('masalah_mitra')->nullable();
            $table->text('solusi_ditawarkan')->nullable();
            $table->text('target_capaian')->nullable();
            $table->text('metode_pelaksanaan')->nullable();
            $table->string('lokasi_kegiatan', 255)->nullable();
            $table->date('jadwal_mulai')->nullable();
            $table->date('jadwal_selesai')->nullable();
            $table->decimal('total_rab', 15, 2)->default(0);
            $table->text('target_luaran')->nullable();
            $table->string('file_proposal', 500)->nullable();
            $table->string('file_surat_mitra', 500)->nullable();
            $table->string('file_pengesahan', 500)->nullable();
            $table->string('file_proposal_name', 255)->nullable();
            $table->string('file_surat_mitra_name', 255)->nullable();
            $table->string('file_pengesahan_name', 255)->nullable();
            $table->string('status', 40)->default('draft');
            $table->string('current_stage', 30)->default('submission');
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('ketua_dosen_id');
            $table->index('ketua_user_id');
            $table->index('mitra_id');
        });

        Schema::create('lppm_pkm_budget_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('community_service_proposal_id');
            $table->foreign('community_service_proposal_id', 'lppm_pkm_bi_prop_fk')
                ->references('id')->on('lppm_community_service_proposals')->cascadeOnDelete();
            $table->string('item_name', 150);
            $table->string('category', 30)->default('other');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 30)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lppm_pkm_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('community_service_proposal_id');
            $table->foreign('community_service_proposal_id', 'lppm_pkm_sh_prop_fk')
                ->references('id')->on('lppm_community_service_proposals')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('transition', 50);
            $table->text('notes')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at');
            $table->json('metadata')->nullable();
        });

        Schema::create('lppm_pkm_admin_verifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('community_service_proposal_id');
            $table->unsignedBigInteger('verifier_user_id');
            $table->foreign('community_service_proposal_id', 'lppm_pkm_av_prop_fk')
                ->references('id')->on('lppm_community_service_proposals')->cascadeOnDelete();
            $table->foreign('verifier_user_id', 'lppm_pkm_av_user_fk')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->string('decision', 30);
            $table->boolean('is_document_complete')->default(false);
            $table->boolean('is_partner_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lppm_pkm_reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('community_service_proposal_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->foreign('community_service_proposal_id', 'lppm_pkm_rv_prop_fk')
                ->references('id')->on('lppm_community_service_proposals')->cascadeOnDelete();
            $table->foreign('reviewer_id', 'lppm_pkm_rv_reviewer_fk')
                ->references('id')->on('lppm_reviewers')->cascadeOnDelete();
            $table->foreign('assigned_by', 'lppm_pkm_rv_assigner_fk')
                ->references('id')->on('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->string('status', 30)->default('assigned');
            $table->string('recommendation', 30)->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('summary')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['community_service_proposal_id', 'reviewer_id'], 'lppm_pkm_rv_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_pkm_reviews');
        Schema::dropIfExists('lppm_pkm_admin_verifications');
        Schema::dropIfExists('lppm_pkm_status_histories');
        Schema::dropIfExists('lppm_pkm_budget_items');
        Schema::dropIfExists('lppm_community_service_proposals');
        Schema::dropIfExists('lppm_partners');
    }
};
