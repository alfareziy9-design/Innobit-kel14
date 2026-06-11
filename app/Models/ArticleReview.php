<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleReview extends Model
{
    protected $fillable = [
        'article_id',
        'reviewer_id',
        'decision',
        'note',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
