<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 30);
            $table->string('type', 60);
            $table->string('severity', 10)->default('info');
            $table->string('title', 200);
            $table->text('body');
            $table->string('action_url', 500)->nullable();
            $table->string('action_label', 50)->nullable();
            $table->string('notifiable_type', 100)->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->json('payload')->nullable();
            $table->string('dedupe_key', 120)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'created_at']);
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['category', 'type']);
            $table->unique('dedupe_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_notifications');
    }
};
