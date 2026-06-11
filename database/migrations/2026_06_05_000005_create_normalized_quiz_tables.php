<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('title', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('article_id');
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('question');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('option_text', 300);
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        DB::table('articles')
            ->whereNotNull('quiz')
            ->orderBy('id')
            ->chunkById(100, function ($articles): void {
                foreach ($articles as $article) {
                    $quiz = json_decode($article->quiz, true);

                    if (! is_array($quiz) || empty($quiz['question']) || empty($quiz['options'])) {
                        continue;
                    }

                    $quizId = DB::table('quizzes')->insertGetId([
                        'article_id' => $article->id,
                        'title' => 'Quiz '.$article->title,
                        'is_active' => true,
                        'created_at' => $article->created_at,
                        'updated_at' => $article->updated_at,
                    ]);

                    $questionId = DB::table('quiz_questions')->insertGetId([
                        'quiz_id' => $quizId,
                        'question' => $quiz['question'],
                        'position' => 1,
                        'created_at' => $article->created_at,
                        'updated_at' => $article->updated_at,
                    ]);

                    foreach (array_values($quiz['options']) as $index => $option) {
                        DB::table('quiz_options')->insert([
                            'quiz_question_id' => $questionId,
                            'option_text' => $option,
                            'is_correct' => $index === (int) ($quiz['correct_option'] ?? 1),
                            'position' => $index + 1,
                            'created_at' => $article->created_at,
                            'updated_at' => $article->updated_at,
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};
