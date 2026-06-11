<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_status', 20)->default('active')->after('role_id');
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->timestamp('read_at')->nullable()->after('message')->index();
            $table->softDeletes();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('action', 80);
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });

        DB::table('users')->whereNull('account_status')->update(['account_status' => 'active']);
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropIndex(['read_at']);
            $table->dropColumn('read_at');
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('account_status');
        });
    }
};
