<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\Category;
use App\Models\LearningCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_page_shows_create_card_and_custom_collection_names(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        LearningCollection::create([
            'user_id' => $user->id,
            'name' => 'Laravel Dasar',
        ]);

        $this->actingAs($user)
            ->get(route('learning.collections'))
            ->assertOk()
            ->assertSee('Buat koleksi baru')
            ->assertSee('Laravel Dasar')
            ->assertSee('Album siap diisi');
    }

    public function test_user_can_create_and_rename_own_collection(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('learning.collections.store'), ['name' => 'Frontend'])
            ->assertRedirect();

        $collection = LearningCollection::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Frontend', $collection->name);

        $this->actingAs($user)
            ->put(route('learning.collections.update', $collection), ['name' => 'Frontend Lanjutan'])
            ->assertRedirect();

        $this->assertSame('Frontend Lanjutan', $collection->fresh()->name);
    }

    public function test_user_cannot_rename_another_users_collection(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $collection = LearningCollection::create([
            'user_id' => $otherUser->id,
            'name' => 'Private',
        ]);

        $this->actingAs($user)
            ->put(route('learning.collections.update', $collection), ['name' => 'Coba Rename'])
            ->assertForbidden();

        $this->assertSame('Private', $collection->fresh()->name);
    }

    public function test_user_can_delete_own_collection_with_items(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'author']);
        $collection = LearningCollection::create([
            'user_id' => $user->id,
            'name' => 'Backend',
        ]);
        $category = Category::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Belajar Delete Koleksi',
            'slug' => 'belajar-delete-koleksi',
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => 'published',
        ]);
        ArticleCollection::create([
            'user_id' => $user->id,
            'collection_id' => $collection->id,
            'article_id' => $article->id,
        ]);

        $this->actingAs($user)
            ->delete(route('learning.collections.destroy', $collection))
            ->assertRedirect();

        $this->assertDatabaseMissing('learning_collections', ['id' => $collection->id]);
        $this->assertDatabaseMissing('article_collections', ['collection_id' => $collection->id]);
    }

    public function test_user_cannot_delete_another_users_collection(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $collection = LearningCollection::create([
            'user_id' => $otherUser->id,
            'name' => 'Private',
        ]);

        $this->actingAs($user)
            ->delete(route('learning.collections.destroy', $collection))
            ->assertForbidden();

        $this->assertDatabaseHas('learning_collections', ['id' => $collection->id]);
    }

    public function test_saved_article_goes_to_default_collection(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Belajar Koleksi',
            'slug' => 'belajar-koleksi',
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->post(route('articles.collection.toggle', $article))
            ->assertRedirect();

        $collection = LearningCollection::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Koleksi Utama', $collection->name);
        $this->assertDatabaseHas('article_collections', [
            'user_id' => $user->id,
            'collection_id' => $collection->id,
            'article_id' => $article->id,
        ]);
    }

    public function test_saved_article_can_go_to_selected_custom_collection(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'author']);
        $collection = LearningCollection::create([
            'user_id' => $user->id,
            'name' => 'Backend',
        ]);
        $category = Category::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Belajar Backend',
            'slug' => 'belajar-backend',
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->post(route('articles.collection.toggle', $article), [
                'collection_id' => $collection->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('article_collections', [
            'user_id' => $user->id,
            'collection_id' => $collection->id,
            'article_id' => $article->id,
        ]);
    }
}
