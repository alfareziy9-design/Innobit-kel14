<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'article_id', 'viewed_at'];
}
