<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleView extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'article_id', 'ip_address', 'user_agent', 'viewed_at'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];
}
