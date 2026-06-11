<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptAnswer extends Model
{
    protected $fillable = ['quiz_attempt_id', 'quiz_question_id', 'quiz_option_id', 'is_correct'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];
}
