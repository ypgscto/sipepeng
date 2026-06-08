<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_publications', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_number', 40)->unique();
            $table->unsignedBigInteger('publication_type_id');
            $table->foreign('publication_type_id', 'lppm_pub_type_fk')
                ->references('id')->on('lppm_publication_types')->restrictOnDelete();
            $table->string('judul', 255);
            $table->text('abstract')->nullable();
            $table->string('journal_or_publisher', 255)->nullable();
            $table->string('issn', 20)->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('doi', 100)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('indexing_label', 100)->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('volume', 30)->nullable();
            $table->string('issue', 30)->nullable();
            $table->string('pages', 50)->nullable();
            $table->string('prodi_id', 50);
            $table->string('prodi_nama_snapshot', 150);
            $table->string('source_type', 30)->default('standalone');
            $table->unsignedBigInteger('research_proposal_id')->nullable();
            $table->unsignedBigInteger('community_service_proposal_id')->nullable();
            $table->string('proposal_number_snapshot', 40)->nullable();
            $table->string('proposal_judul_snapshot', 255)->nullable();
            $table->unsignedBigInteger('output_type_id')->nullable();
            $table->foreign('research_proposal_id', 'lppm_pub_res_prop_fk')
                ->references('id')->on('lppm_research_proposals')->nullOnDelete();
            $table->foreign('community_service_proposal_id', 'lppm_pub_pkm_prop_fk')
                ->references('id')->on('lppm_community_service_proposals')->nullOnDelete();
            $table->foreign('output_type_id', 'lppm_pub_output_fk')
                ->references('id')->on('lppm_output_types')->nullOnDelete();
            $table->string('file_manuscript', 500)->nullable();
            $table->string('file_acceptance_letter', 500)->nullable();
            $table->string('file_published', 500)->nullable();
            $table->string('file_other', 500)->nullable();
            $table->string('file_manuscript_name', 255)->nullable();
            $table->string('file_acceptance_letter_name', 255)->nullable();
            $table->string('file_published_name', 255)->nullable();
            $table->string('file_other_name', 255)->nullable();
            $table->string('status', 40)->default('draft');
            $table->string('current_stage', 30)->default('submission');
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes_internal')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('publication_year');
            $table->index('prodi_id');
            $table->index('source_type');
        });

        Schema::create('lppm_publication_authors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('publication_id');
            $table->foreign('publication_id', 'lppm_pub_auth_pub_fk')
                ->references('id')->on('lppm_publications')->cascadeOnDelete();
            $table->unsignedSmallInteger('author_order')->default(1);
            $table->string('role', 20)->default('co_author');
            $table->string('dosen_id', 50);
            $table->string('dosen_nama_snapshot', 150);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prodi_id', 50)->nullable();
            $table->string('prodi_nama_snapshot', 150)->nullable();
            $table->string('affiliation_snapshot', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('lppm_publication_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('publication_id');
            $table->foreign('publication_id', 'lppm_pub_sh_pub_fk')
                ->references('id')->on('lppm_publications')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('transition', 50);
            $table->text('notes')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at');
            $table->json('metadata')->nullable();
        });

        Schema::create('lppm_publication_verifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('publication_id');
            $table->foreign('publication_id', 'lppm_pub_ver_pub_fk')
                ->references('id')->on('lppm_publications')->cascadeOnDelete();
            $table->foreignId('verifier_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 30);
            $table->boolean('is_document_complete')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lppm_ip_registrations', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_number', 40)->unique();
            $table->unsignedBigInteger('ip_type_id');
            $table->foreign('ip_type_id', 'lppm_ip_type_fk')
                ->references('id')->on('lppm_ip_types')->restrictOnDelete();
            $table->string('judul', 255);
            $table->text('description')->nullable();
            $table->string('registration_body', 100)->nullable();
            $table->string('application_number', 80)->nullable();
            $table->string('certificate_number', 80)->nullable();
            $table->date('application_date')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('ownership_type', 30)->default('institution');
            $table->string('prodi_id', 50);
            $table->string('prodi_nama_snapshot', 150);
            $table->string('source_type', 30)->default('standalone');
            $table->unsignedBigInteger('research_proposal_id')->nullable();
            $table->unsignedBigInteger('community_service_proposal_id')->nullable();
            $table->string('proposal_number_snapshot', 40)->nullable();
            $table->string('proposal_judul_snapshot', 255)->nullable();
            $table->foreign('research_proposal_id', 'lppm_ip_res_prop_fk')
                ->references('id')->on('lppm_research_proposals')->nullOnDelete();
            $table->foreign('community_service_proposal_id', 'lppm_ip_pkm_prop_fk')
                ->references('id')->on('lppm_community_service_proposals')->nullOnDelete();
            $table->string('file_application', 500)->nullable();
            $table->string('file_statement', 500)->nullable();
            $table->string('file_certificate', 500)->nullable();
            $table->string('file_supporting', 500)->nullable();
            $table->string('file_application_name', 255)->nullable();
            $table->string('file_statement_name', 255)->nullable();
            $table->string('file_certificate_name', 255)->nullable();
            $table->string('file_supporting_name', 255)->nullable();
            $table->string('status', 40)->default('draft');
            $table->string('current_stage', 30)->default('submission');
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes_internal')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('prodi_id');
            $table->index('source_type');
        });

        Schema::create('lppm_ip_inventors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ip_registration_id');
            $table->foreign('ip_registration_id', 'lppm_ip_inv_reg_fk')
                ->references('id')->on('lppm_ip_registrations')->cascadeOnDelete();
            $table->unsignedSmallInteger('inventor_order')->default(1);
            $table->string('dosen_id', 50);
            $table->string('dosen_nama_snapshot', 150);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prodi_id', 50)->nullable();
            $table->string('prodi_nama_snapshot', 150)->nullable();
            $table->decimal('contribution_pct', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('lppm_ip_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ip_registration_id');
            $table->foreign('ip_registration_id', 'lppm_ip_sh_reg_fk')
                ->references('id')->on('lppm_ip_registrations')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('transition', 50);
            $table->text('notes')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at');
            $table->json('metadata')->nullable();
        });

        Schema::create('lppm_ip_verifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ip_registration_id');
            $table->foreign('ip_registration_id', 'lppm_ip_ver_reg_fk')
                ->references('id')->on('lppm_ip_registrations')->cascadeOnDelete();
            $table->foreignId('verifier_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 30);
            $table->boolean('is_document_complete')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lppm_research_ethics_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('application_number', 40)->unique();
            $table->unsignedBigInteger('research_proposal_id');
            $table->foreign('research_proposal_id', 'lppm_eth_res_prop_fk')
                ->references('id')->on('lppm_research_proposals')->restrictOnDelete();
            $table->string('proposal_number_snapshot', 40);
            $table->string('proposal_judul_snapshot', 255);
            $table->string('ketua_dosen_id', 50);
            $table->string('ketua_dosen_nama_snapshot', 150);
            $table->foreignId('ketua_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prodi_id', 50);
            $table->string('prodi_nama_snapshot', 150);
            $table->string('study_type', 30)->nullable();
            $table->text('population_description')->nullable();
            $table->string('risk_level', 20)->nullable();
            $table->text('data_collection_method')->nullable();
            $table->boolean('informed_consent_required')->default(false);
            $table->boolean('conflict_of_interest_declared')->default(false);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('file_protocol', 500)->nullable();
            $table->string('file_ethics_application', 500)->nullable();
            $table->string('file_consent_form', 500)->nullable();
            $table->string('file_approval_letter', 500)->nullable();
            $table->string('file_protocol_name', 255)->nullable();
            $table->string('file_ethics_application_name', 255)->nullable();
            $table->string('file_consent_form_name', 255)->nullable();
            $table->string('file_approval_letter_name', 255)->nullable();
            $table->string('status', 40)->default('draft');
            $table->string('current_stage', 30)->default('submission');
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('committee_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('research_proposal_id');
            $table->index('prodi_id');
        });

        Schema::create('lppm_research_ethics_reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ethics_application_id');
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreign('ethics_application_id', 'lppm_eth_rv_app_fk')
                ->references('id')->on('lppm_research_ethics_applications')->cascadeOnDelete();
            $table->foreign('reviewer_id', 'lppm_eth_rv_reviewer_fk')
                ->references('id')->on('lppm_reviewers')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->string('decision', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lppm_research_ethics_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ethics_application_id');
            $table->foreign('ethics_application_id', 'lppm_eth_sh_app_fk')
                ->references('id')->on('lppm_research_ethics_applications')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('transition', 50);
            $table->text('notes')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at');
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_research_ethics_status_histories');
        Schema::dropIfExists('lppm_research_ethics_reviews');
        Schema::dropIfExists('lppm_research_ethics_applications');
        Schema::dropIfExists('lppm_ip_verifications');
        Schema::dropIfExists('lppm_ip_status_histories');
        Schema::dropIfExists('lppm_ip_inventors');
        Schema::dropIfExists('lppm_ip_registrations');
        Schema::dropIfExists('lppm_publication_verifications');
        Schema::dropIfExists('lppm_publication_status_histories');
        Schema::dropIfExists('lppm_publication_authors');
        Schema::dropIfExists('lppm_publications');
    }
};
