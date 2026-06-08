<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lppm_letter_types', function (Blueprint $table): void {
            $table->string('code', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lppm_letter_types', function (Blueprint $table): void {
            $table->string('code', 30)->change();
        });
    }
};
