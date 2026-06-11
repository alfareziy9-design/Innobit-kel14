<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
        });

        Schema::create('article_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('decision', 20);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_reviews');

        DB::table('articles')
            ->whereNotIn('status', ['draft', 'published'])
            ->update(['status' => 'draft']);

        Schema::table('articles', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('published')->change();
        });
    }
};
