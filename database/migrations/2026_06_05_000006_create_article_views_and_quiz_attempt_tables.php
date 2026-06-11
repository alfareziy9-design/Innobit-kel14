<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['user_id', 'article_id', 'viewed_at']);
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('score')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            $table->index(['user_id', 'article_id', 'quiz_id']);
        });

        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('quiz_option_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        if (Schema::hasTable('histories')) {
            DB::table('histories')->orderBy('id')->chunkById(100, function ($histories): void {
                foreach ($histories as $history) {
                    DB::table('article_views')->insert([
                        'user_id' => $history->user_id,
                        'article_id' => $history->article_id,
                        'viewed_at' => $history->viewed_at,
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('article_views');
    }
};
