<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siakad_reference_cache', function (Blueprint $table): void {
            $table->id();
            $table->string('resource_key', 50)->unique();
            $table->longText('payload');
            $table->unsignedInteger('record_count')->default(0);
            $table->json('meta')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamp('expires_at')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->timestamps();

            $table->index('expires_at');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siakad_reference_cache');
    }
};
