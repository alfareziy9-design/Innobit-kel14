<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_merges_english_category_aliases_into_indonesian_categories(): void
    {
        $author = User::factory()->create(['role' => 'admin']);
        $design = Category::create(['name' => 'Design', 'slug' => 'design']);
        $article = Article::create([
            'category_id' => $design->id,
            'author_id' => $author->id,
            'title' => 'Artikel Design Lama',
            'slug' => 'artikel-design-lama',
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => 'published',
        ]);

        $this->seed(DatabaseSeeder::class);

        $desain = Category::where('slug', 'desain')->firstOrFail();

        $this->assertSame($desain->id, $article->fresh()->category_id);
        $this->assertDatabaseMissing('categories', ['slug' => 'design']);
        $this->assertDatabaseMissing('categories', ['slug' => 'productivity']);
        $this->assertDatabaseHas('categories', ['name' => 'Produktivitas', 'slug' => 'produktivitas']);
    }
}
