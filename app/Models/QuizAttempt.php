<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = ['user_id', 'article_id', 'quiz_id', 'score', 'started_at', 'submitted_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
