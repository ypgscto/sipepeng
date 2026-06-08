<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lppm_letter_types', function (Blueprint $table): void {
            $table->string('applies_to', 30)->default('general')->after('requires_approval');
            $table->string('number_format_pattern', 100)->nullable()->after('applies_to');
            $table->boolean('requires_proposal_link')->default(false)->after('number_format_pattern');
            $table->boolean('requires_partner_link')->default(false)->after('requires_proposal_link');
            $table->string('min_proposal_status', 40)->default('approved')->after('requires_partner_link');
            $table->boolean('allow_dosen_create')->default(true)->after('min_proposal_status');
        });

        Schema::table('lppm_document_templates', function (Blueprint $table): void {
            $table->string('render_engine', 20)->default('blade_pdf')->after('variables_schema');
            $table->string('blade_view', 150)->nullable()->after('render_engine');
        });
    }

    public function down(): void
    {
        Schema::table('lppm_document_templates', function (Blueprint $table): void {
            $table->dropColumn(['render_engine', 'blade_view']);
        });

        Schema::table('lppm_letter_types', function (Blueprint $table): void {
            $table->dropColumn([
                'applies_to', 'number_format_pattern', 'requires_proposal_link',
                'requires_partner_link', 'min_proposal_status', 'allow_dosen_create',
            ]);
        });
    }
};
