<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_funding_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('source_category', 20)->default('internal');
            $table->string('institution_name', 150)->nullable();
            $table->boolean('requires_contract')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_focus_areas', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('lppm_focus_areas')->nullOnDelete();
            $table->char('color', 7)->nullable();
            $table->year('year_start')->nullable();
            $table->year('year_end')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_science_clusters', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('feeder_code', 20)->nullable()->unique();
            $table->foreignId('parent_id')->nullable()->constrained('lppm_science_clusters')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_output_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('applies_to', 20)->default('both');
            $table->boolean('is_measurable')->default(true);
            $table->string('unit_label', 30)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_partner_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('requires_legal_document')->default(false);
            $table->string('icon', 30)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_document_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('module_type', 20)->default('general');
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_ip_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('registration_body', 100)->nullable();
            $table->unsignedSmallInteger('typical_duration_months')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_publication_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('indexing_type', 20)->nullable();
            $table->boolean('requires_issn_isbn')->default(false);
            $table->string('feeder_code', 20)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_proposal_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('proposal_type', 20)->default('both');
            $table->string('stage', 30);
            $table->char('color', 7)->nullable();
            $table->boolean('is_terminal')->default(false);
            $table->boolean('is_editable_by_proposer')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_research_schemes', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('academic_year_label', 20)->nullable();
            $table->decimal('max_budget', 15, 2)->nullable();
            $table->unsignedTinyInteger('min_team_members')->nullable();
            $table->unsignedTinyInteger('max_team_members')->nullable();
            $table->boolean('requires_ethics_approval')->default(false);
            $table->date('submission_deadline')->nullable();
            $table->string('guideline_url', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_community_service_schemes', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('academic_year_label', 20)->nullable();
            $table->decimal('max_budget', 15, 2)->nullable();
            $table->unsignedTinyInteger('min_team_members')->nullable();
            $table->unsignedTinyInteger('max_team_members')->nullable();
            $table->boolean('requires_partner')->default(true);
            $table->date('submission_deadline')->nullable();
            $table->string('guideline_url', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_document_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('template_code', 40)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('document_category_id')->nullable()->constrained('lppm_document_categories')->nullOnDelete();
            $table->string('module_type', 20)->default('general');
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size')->default(0);
            $table->string('version', 20)->default('1.0');
            $table->boolean('is_default')->default(false);
            $table->json('variables_schema')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_letter_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('letter_prefix', 20)->nullable();
            $table->foreignId('document_template_id')->nullable()->constrained('lppm_document_templates')->nullOnDelete();
            $table->boolean('requires_approval')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_reviewers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->text('expertise_notes')->nullable();
            $table->foreignId('science_cluster_id')->nullable()->constrained('lppm_science_clusters')->nullOnDelete();
            $table->foreignId('focus_area_id')->nullable()->constrained('lppm_focus_areas')->nullOnDelete();
            $table->unsignedTinyInteger('max_active_reviews')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('appointed_at')->nullable();
            $table->foreignId('appointed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lppm_research_scheme_funding_sources', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('research_scheme_id');
            $table->unsignedBigInteger('funding_source_id');
            $table->unique(['research_scheme_id', 'funding_source_id'], 'lppm_rs_fs_unique');
            $table->foreign('research_scheme_id', 'lppm_rs_fs_scheme_fk')
                ->references('id')->on('lppm_research_schemes')->cascadeOnDelete();
            $table->foreign('funding_source_id', 'lppm_rs_fs_source_fk')
                ->references('id')->on('lppm_funding_sources')->cascadeOnDelete();
        });

        Schema::create('lppm_community_service_scheme_funding_sources', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('community_service_scheme_id');
            $table->unsignedBigInteger('funding_source_id');
            $table->unique(['community_service_scheme_id', 'funding_source_id'], 'lppm_css_fs_unique');
            $table->foreign('community_service_scheme_id', 'lppm_css_fs_scheme_fk')
                ->references('id')->on('lppm_community_service_schemes')->cascadeOnDelete();
            $table->foreign('funding_source_id', 'lppm_css_fs_source_fk')
                ->references('id')->on('lppm_funding_sources')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_community_service_scheme_funding_sources');
        Schema::dropIfExists('lppm_research_scheme_funding_sources');
        Schema::dropIfExists('lppm_reviewers');
        Schema::dropIfExists('lppm_letter_types');
        Schema::dropIfExists('lppm_document_templates');
        Schema::dropIfExists('lppm_community_service_schemes');
        Schema::dropIfExists('lppm_research_schemes');
        Schema::dropIfExists('lppm_proposal_statuses');
        Schema::dropIfExists('lppm_publication_types');
        Schema::dropIfExists('lppm_ip_types');
        Schema::dropIfExists('lppm_document_categories');
        Schema::dropIfExists('lppm_partner_types');
        Schema::dropIfExists('lppm_output_types');
        Schema::dropIfExists('lppm_science_clusters');
        Schema::dropIfExists('lppm_focus_areas');
        Schema::dropIfExists('lppm_funding_sources');
    }
};
