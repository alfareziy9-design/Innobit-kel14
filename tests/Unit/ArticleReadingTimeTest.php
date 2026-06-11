<?php

namespace Tests\Unit;

use App\Models\Article;
use PHPUnit\Framework\TestCase;

class ArticleReadingTimeTest extends TestCase
{
    public function test_content_up_to_200_words_takes_one_minute(): void
    {
        $article = new Article(['content' => $this->words(200)]);

        $this->assertSame(1, $article->reading_minutes);
    }

    public function test_content_over_200_words_is_rounded_up(): void
    {
        $article = new Article(['content' => $this->words(201)]);

        $this->assertSame(2, $article->reading_minutes);
    }

    public function test_html_tags_are_not_counted_as_words(): void
    {
        $article = new Article([
            'content' => '<article><h2>Judul</h2><p>'.$this->words(199).'</p></article>',
        ]);

        $this->assertSame(1, $article->reading_minutes);
    }

    public function test_html_entities_and_unicode_words_are_counted(): void
    {
        $article = new Article([
            'content' => '<p>'.implode(' ', array_fill(0, 100, 'belajar')).'&nbsp;'
                .implode(' ', array_fill(0, 101, 'caf&#233;')).'</p>',
        ]);

        $this->assertSame(2, $article->reading_minutes);
    }

    public function test_empty_content_has_a_one_minute_minimum(): void
    {
        $article = new Article(['content' => '']);

        $this->assertSame(1, $article->reading_minutes);
    }

    private function words(int $count): string
    {
        return implode(' ', array_fill(0, $count, 'kata'));
    }
}
