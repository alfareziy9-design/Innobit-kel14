<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\ArticleView;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_all_roles_can_open_their_profile(): void
    {
        foreach (['user', 'author', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('profile.show'))
                ->assertOk()
                ->assertSee($user->name)
                ->assertSee($user->email);
        }
    }

    public function test_profile_statistics_only_count_the_authenticated_user_activity(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Profile Stats', 'slug' => 'profile-stats']);
        $article = $this->createArticle($author, $category, 'Artikel Statistik', 'published');
        $otherArticle = $this->createArticle($author, $category, 'Artikel Statistik Lain', 'published');
        $quiz = Quiz::create(['article_id' => $article->id, 'title' => 'Quiz Statistik', 'is_active' => true]);

        ArticleView::create(['user_id' => $user->id, 'article_id' => $article->id, 'viewed_at' => now()]);
        ArticleView::create(['user_id' => $user->id, 'article_id' => $article->id, 'viewed_at' => now()]);
        ArticleView::create(['user_id' => $user->id, 'article_id' => $otherArticle->id, 'viewed_at' => now()]);
        ArticleView::create(['user_id' => $otherUser->id, 'article_id' => $article->id, 'viewed_at' => now()]);
        Favorite::create(['user_id' => $user->id, 'article_id' => $article->id]);
        Favorite::create(['user_id' => $otherUser->id, 'article_id' => $otherArticle->id]);
        ArticleCollection::create(['user_id' => $user->id, 'article_id' => $article->id]);
        QuizAttempt::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'quiz_id' => $quiz->id,
            'score' => 100,
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertViewHas('learningStats', [
                'articles_read' => 2,
                'favorites' => 1,
                'collections' => 1,
                'quiz_attempts' => 1,
            ])
            ->assertViewHas('articleStats', null);
    }

    public function test_author_profile_shows_article_status_statistics(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $otherAuthor = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Writer Stats', 'slug' => 'writer-stats']);

        foreach (['draft', 'review', 'published', 'rejected'] as $status) {
            $this->createArticle($author, $category, "Artikel {$status}", $status);
        }
        $this->createArticle($otherAuthor, $category, 'Artikel Penulis Lain', 'published');

        $this->actingAs($author)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertViewHas('articleStats', [
                'total' => 4,
                'draft' => 1,
                'review' => 1,
                'published' => 1,
                'rejected' => 1,
            ]);
    }

    public function test_name_can_be_updated_without_current_password(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Baru']);
    }

    public function test_email_change_requires_correct_current_password_and_unique_email(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'password' => Hash::make('password-lama'),
        ]);
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'baru@example.com',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'baru@example.com',
                'current_password' => 'salah',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $otherUser->email,
                'current_password' => 'password-lama',
            ])
            ->assertSessionHasErrors('email');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'baru@example.com',
                'current_password' => 'password-lama',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'baru@example.com']);
    }

    public function test_password_update_requires_current_password_and_hashes_new_password(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'password' => Hash::make('password-lama'),
        ]);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'salah',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_photo_can_be_uploaded_replaced_and_removed(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->put(route('profile.photo.update'), ['photo' => $this->fakePngUpload('first.png')])
            ->assertSessionHasNoErrors();

        $firstPath = $user->fresh()->photo;
        Storage::disk('public')->assertExists($firstPath);

        $this->actingAs($user)
            ->put(route('profile.photo.update'), ['photo' => $this->fakePngUpload('second.png')])
            ->assertSessionHasNoErrors();

        $secondPath = $user->fresh()->photo;
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $this->actingAs($user)
            ->delete(route('profile.photo.destroy'))
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->photo);
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_invalid_profile_photo_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->put(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->create('avatar.gif', 10, 'image/gif'),
            ])
            ->assertSessionHasErrors('photo');
    }

    public function test_failed_photo_update_keeps_old_photo_and_cleans_new_file(): void
    {
        $oldPath = 'profile-photos/original.png';
        Storage::disk('public')->put($oldPath, 'original');
        $user = User::factory()->create(['role' => 'user', 'photo' => $oldPath]);

        DB::statement("
            CREATE TRIGGER fail_profile_photo_update
            BEFORE UPDATE ON users
            BEGIN
                SELECT RAISE(ABORT, 'forced update failure');
            END
        ");

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)
                ->put(route('profile.photo.update'), ['photo' => $this->fakePngUpload('replacement.png')]);

            $this->fail('Database update should have failed.');
        } catch (QueryException) {
            // Expected: the new file must be removed while the old photo remains.
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS fail_profile_photo_update');
        }

        $this->assertSame($oldPath, $user->fresh()->photo);
        Storage::disk('public')->assertExists($oldPath);
        $this->assertCount(1, Storage::disk('public')->allFiles('profile-photos'));
    }

    public function test_profile_and_navbar_use_initial_when_photo_is_missing(): void
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'Budi', 'photo' => null]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Budi')
            ->assertDontSee('<img src="/storage/profile-photos/', false);
    }

    private function createArticle(User $author, Category $category, string $title, string $status): Article
    {
        return Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => $title,
            'slug' => str($title)->slug(),
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => $status,
        ]);
    }

    private function fakePngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'profile_');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
