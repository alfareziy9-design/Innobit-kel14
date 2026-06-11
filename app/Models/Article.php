<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'review', 'published', 'rejected'];

    public const WORDS_PER_MINUTE = 200;

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'summary',
        'content',
        'quiz',
        'thumbnail_media_id',
        'status',
    ];

    protected $casts = [
        'quiz' => 'array',
    ];

    public function getReadingMinutesAttribute(): int
    {
        $content = html_entity_decode(
            strip_tags((string) $this->content),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $wordCount = preg_match_all('/[\p{L}\p{N}]+(?:[\'-][\p{L}\p{N}]+)*/u', $content);

        return max(1, (int) ceil($wordCount / self::WORDS_PER_MINUTE));
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function thumbnailMedia()
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function normalizedQuiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function views()
    {
        return $this->hasMany(ArticleView::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function collections()
    {
        return $this->hasMany(ArticleCollection::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function reviews()
    {
        return $this->hasMany(ArticleReview::class)->latest();
    }

    public function latestReview()
    {
        return $this->hasOne(ArticleReview::class)->latestOfMany();
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class)->latest();
    }

    public function pendingRevision()
    {
        return $this->hasOne(ArticleRevision::class)->where('status', 'review')->latestOfMany();
    }

    public function latestRevision()
    {
        return $this->hasOne(ArticleRevision::class)->latestOfMany();
    }
}
