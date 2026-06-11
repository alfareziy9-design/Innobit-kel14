<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleCollection extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'article_id', 'collection_id'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function collection()
    {
        return $this->belongsTo(LearningCollection::class, 'collection_id');
    }
}
