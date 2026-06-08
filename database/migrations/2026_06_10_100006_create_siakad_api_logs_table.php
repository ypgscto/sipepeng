<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siakad_api_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('purpose', 50);
            $table->string('http_method', 10);
            $table->string('endpoint');
            $table->json('request_query')->nullable();
            $table->json('request_body')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('is_success')->default(false);
            $table->text('error_message')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('correlation_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('purpose');
            $table->index('is_success');
            $table->index('endpoint');
            $table->index('created_at');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siakad_api_logs');
    }
};
