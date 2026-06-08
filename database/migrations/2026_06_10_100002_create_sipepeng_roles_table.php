<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sipepeng_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('siakad_map_type', 30)->nullable();
            $table->string('siakad_map_key', 50)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['siakad_map_type', 'siakad_map_key']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sipepeng_roles');
    }
};
