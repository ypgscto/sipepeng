<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'siakad_user_id')) {
                $table->string('siakad_user_id', 50)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'siakad_login')) {
                $table->string('siakad_login', 150)->nullable()->unique()->after('siakad_user_id');
            }
            if (! Schema::hasColumn('users', 'user_category')) {
                $table->string('user_category', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'jenis_user')) {
                $table->string('jenis_user', 10)->nullable()->after('user_category');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password');
            }
            if (! Schema::hasColumn('users', 'is_allowed_login')) {
                $table->boolean('is_allowed_login')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('users', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('is_allowed_login');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = [
                'siakad_user_id',
                'siakad_login',
                'user_category',
                'jenis_user',
                'is_active',
                'is_allowed_login',
                'synced_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
