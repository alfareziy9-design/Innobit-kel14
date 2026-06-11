<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningCollection extends Model
{
    protected $fillable = ['user_id', 'name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ArticleCollection::class, 'collection_id');
    }
}
