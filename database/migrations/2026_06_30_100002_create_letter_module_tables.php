<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_letter_number_sequences', function (Blueprint $table): void {
            $table->id();
            $table->string('letter_prefix', 30);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
            $table->unique(['letter_prefix', 'year'], 'lppm_letter_seq_unique');
        });

        Schema::create('lppm_letters', function (Blueprint $table): void {
            $table->id();
            $table->string('internal_number', 50)->unique();
            $table->string('letter_number', 50)->nullable()->unique();
            $table->foreignId('letter_type_id')->constrained('lppm_letter_types')->restrictOnDelete();
            $table->foreignId('document_template_id')->nullable()->constrained('lppm_document_templates')->nullOnDelete();
            $table->string('letter_prefix_snapshot', 30)->nullable();
            $table->string('perihal', 255);
            $table->date('letter_date');
            $table->string('place_of_issue', 100)->nullable();
            $table->json('merge_variables')->nullable();
            $table->unsignedBigInteger('research_proposal_id')->nullable();
            $table->unsignedBigInteger('community_service_proposal_id')->nullable();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->unsignedBigInteger('publication_id')->nullable();
            $table->unsignedBigInteger('ip_registration_id')->nullable();
            $table->string('proposal_number_snapshot', 50)->nullable();
            $table->string('proposal_judul_snapshot', 500)->nullable();
            $table->string('ketua_dosen_id', 50)->nullable();
            $table->string('ketua_dosen_nama_snapshot', 150)->nullable();
            $table->string('prodi_id', 50)->nullable();
            $table->string('prodi_nama_snapshot', 150)->nullable();
            $table->string('mitra_nama_snapshot', 200)->nullable();
            $table->text('mitra_alamat_snapshot')->nullable();
            $table->string('reviewer_nama_snapshot', 150)->nullable();
            $table->string('recipient_external_name', 200)->nullable();
            $table->string('recipient_external_institution', 200)->nullable();
            $table->text('recipient_external_address')->nullable();
            $table->date('event_date')->nullable();
            $table->string('event_time', 20)->nullable();
            $table->string('event_location', 255)->nullable();
            $table->text('event_agenda')->nullable();
            $table->text('body_content')->nullable();
            $table->string('status', 40)->default('draft');
            $table->string('current_stage', 30)->default('submission');
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->string('file_pdf', 500)->nullable();
            $table->string('file_pdf_name', 255)->nullable();
            $table->string('file_signed_scan', 500)->nullable();
            $table->string('file_signed_scan_name', 255)->nullable();
            $table->timestamp('signed_uploaded_at')->nullable();
            $table->foreignId('signed_uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes_internal')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('research_proposal_id', 'lppm_letters_research_fk')
                ->references('id')->on('lppm_research_proposals')->nullOnDelete();
            $table->foreign('community_service_proposal_id', 'lppm_letters_pkm_fk')
                ->references('id')->on('lppm_community_service_proposals')->nullOnDelete();
            $table->foreign('partner_id', 'lppm_letters_partner_fk')
                ->references('id')->on('lppm_partners')->nullOnDelete();
            $table->foreign('reviewer_id', 'lppm_letters_reviewer_fk')
                ->references('id')->on('lppm_reviewers')->nullOnDelete();
            $table->foreign('publication_id', 'lppm_letters_pub_fk')
                ->references('id')->on('lppm_publications')->nullOnDelete();
            $table->foreign('ip_registration_id', 'lppm_letters_ip_fk')
                ->references('id')->on('lppm_ip_registrations')->nullOnDelete();

            $table->index('status');
            $table->index('letter_type_id');
            $table->index('letter_date');
            $table->index('research_proposal_id');
            $table->index('community_service_proposal_id');
        });

        Schema::create('lppm_letter_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_id')->constrained('lppm_letters')->cascadeOnDelete();
            $table->string('recipient_type', 30)->default('external');
            $table->string('name', 200);
            $table->string('email', 150)->nullable();
            $table->string('institution', 200)->nullable();
            $table->string('dosen_id', 50)->nullable();
            $table->string('dosen_nama_snapshot', 150)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lppm_letter_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_id')->constrained('lppm_letters')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('transition', 50);
            $table->text('notes')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('lppm_letter_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_id')->constrained('lppm_letters')->cascadeOnDelete();
            $table->foreignId('approver_user_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 30);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_letter_approvals');
        Schema::dropIfExists('lppm_letter_status_histories');
        Schema::dropIfExists('lppm_letter_recipients');
        Schema::dropIfExists('lppm_letters');
        Schema::dropIfExists('lppm_letter_number_sequences');
    }
};
