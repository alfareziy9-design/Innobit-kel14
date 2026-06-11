<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media')->nullOnDelete()->cascadeOnUpdate();
            $table->string('title', 200);
            $table->string('slug', 220);
            $table->text('summary');
            $table->longText('content');
            $table->json('quiz_data')->nullable();
            $table->string('status', 20)->default('review');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['article_id', 'status']);
            $table->index(['author_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_revisions');
    }
};
