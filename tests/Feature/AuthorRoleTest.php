<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_user_cannot_access_writer_or_admin_pages(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('author.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('articles.create'))->assertForbidden();
    }

    public function test_author_can_access_dashboard_and_create_draft_article(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Programming', 'slug' => 'programming']);

        $this->actingAs($author)->get(route('author.dashboard'))->assertOk();

        $response = $this->actingAs($author)->post(route('articles.store'), [
            'title' => 'Belajar Laravel Routes',
            'category_id' => $category->id,
            'summary' => 'Ringkasan singkat tentang routes.',
            'content' => 'Isi artikel tentang Laravel routes untuk penulis.',
            'status' => 'published',
            'thumbnail' => $this->fakePngUpload(),
            'quizzes' => $this->quizPayload(),
        ]);

        $response->assertRedirect(route('author.dashboard'));

        $this->assertDatabaseHas('articles', [
            'title' => 'Belajar Laravel Routes',
            'author_id' => $author->id,
            'status' => 'draft',
        ]);

        $article = Article::with('thumbnailMedia')->where('title', 'Belajar Laravel Routes')->firstOrFail();

        $this->assertSame('public', $article->thumbnailMedia->disk);
        $this->assertStringStartsWith('article/thumbnails/', $article->thumbnailMedia->path);
        $this->assertStringContainsString('/storage/article/thumbnails/', $article->thumbnailMedia->url);
        Storage::disk('public')->assertExists($article->thumbnailMedia->path);
    }

    public function test_article_create_form_starts_with_one_quiz_and_add_button(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        Category::create(['name' => 'Programming', 'slug' => 'programming']);

        $this->actingAs($author)
            ->get(route('articles.create'))
            ->assertOk()
            ->assertSee('+ Tambah Quiz')
            ->assertSee('name="quizzes[0][question]"', false)
            ->assertSee('data-quiz-index="1"', false)
            ->assertSee('hidden rounded-xl border border-lime-200 bg-white p-4 shadow-sm', false);
    }

    public function test_article_requires_at_least_one_quiz(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Required Quiz', 'slug' => 'required-quiz']);

        $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Artikel Tanpa Quiz',
            'category_id' => $category->id,
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => 'published',
            'thumbnail' => $this->fakePngUpload(),
        ])->assertSessionHasErrors('quizzes');
    }

    public function test_article_can_save_up_to_three_quizzes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Multi Quiz', 'slug' => 'multi-quiz']);

        $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Artikel Multi Quiz',
            'category_id' => $category->id,
            'summary' => 'Ringkasan multi quiz.',
            'content' => 'Isi artikel multi quiz.',
            'status' => 'published',
            'thumbnail' => $this->fakePngUpload(),
            'quizzes' => [
                [
                    'question' => 'Pertanyaan pertama?',
                    'options' => ['A1', 'B1', 'C1'],
                    'correct_option' => 0,
                ],
                [
                    'question' => 'Pertanyaan kedua?',
                    'options' => ['A2', 'B2', 'C2'],
                    'correct_option' => 1,
                ],
                [
                    'question' => 'Pertanyaan ketiga?',
                    'options' => ['A3', 'B3', 'C3'],
                    'correct_option' => 2,
                ],
            ],
        ])->assertRedirect(route('admin.dashboard'));

        $article = Article::with('normalizedQuiz.questions.options')
            ->where('title', 'Artikel Multi Quiz')
            ->firstOrFail();

        $this->assertCount(3, $article->normalizedQuiz->questions);

        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('3 soal')
            ->assertSee('Pertanyaan pertama?')
            ->assertSee('Pertanyaan kedua?')
            ->assertSee('Pertanyaan ketiga?');
    }

    public function test_author_cannot_manage_other_author_articles(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $otherAuthor = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Design', 'slug' => 'design']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $otherAuthor->id,
            'title' => 'Artikel Orang Lain',
            'slug' => 'artikel-orang-lain',
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => 'draft',
        ]);

        $this->actingAs($author)->get(route('articles.edit', $article))->assertForbidden();
        $this->actingAs($author)->delete(route('articles.destroy', $article))->assertForbidden();
    }

    public function test_author_cannot_access_category_or_admin_dashboard(): void
    {
        $author = User::factory()->create(['role' => 'author']);

        $this->actingAs($author)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($author)->get(route('kategori.index'))->assertForbidden();
    }

    public function test_admin_can_publish_and_manage_any_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Productivity', 'slug' => 'productivity']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Draft Penulis',
            'slug' => 'draft-penulis',
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->put(route('articles.update', $article), [
            'title' => 'Draft Penulis Published',
            'category_id' => $category->id,
            'summary' => 'Ringkasan artikel diperbarui.',
            'content' => 'Konten artikel diperbarui.',
            'status' => 'published',
            'quizzes' => $this->quizPayload(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Draft Penulis Published',
            'status' => 'published',
        ]);
    }

    public function test_article_thumbnail_does_not_accept_gif(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Programming', 'slug' => 'programming']);

        $response = $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Thumbnail GIF',
            'category_id' => $category->id,
            'summary' => 'Ringkasan singkat.',
            'content' => 'Isi artikel.',
            'status' => 'published',
            'thumbnail' => $this->fakeGifUpload(),
            'quizzes' => $this->quizPayload(),
        ]);

        $response->assertSessionHasErrors('thumbnail');
    }

    public function test_replacing_thumbnail_updates_media_then_deletes_old_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Storage', 'slug' => 'storage']);
        $oldPath = 'article/thumbnails/old-thumbnail.png';

        Storage::disk('public')->put($oldPath, 'old image');

        $oldMedia = Media::create([
            'user_id' => $admin->id,
            'disk' => 'public',
            'folder' => 'article/thumbnails',
            'path' => $oldPath,
            'original_name' => 'old-thumbnail.png',
            'mime_type' => 'image/png',
            'size' => 9,
            'usage' => 'thumbnail',
        ]);

        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Artikel Storage',
            'slug' => 'artikel-storage',
            'summary' => 'Ringkasan artikel storage.',
            'content' => 'Konten artikel storage.',
            'thumbnail_media_id' => $oldMedia->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($admin)->put(route('articles.update', $article), [
            'title' => 'Artikel Storage Baru',
            'category_id' => $category->id,
            'summary' => 'Ringkasan artikel storage baru.',
            'content' => 'Konten artikel storage baru.',
            'status' => 'published',
            'thumbnail' => $this->fakePngUpload(),
            'quizzes' => $this->quizPayload(),
        ]);

        $response->assertSessionHasNoErrors();

        $article->refresh()->load('thumbnailMedia');

        $this->assertNotSame($oldMedia->id, $article->thumbnail_media_id);
        $this->assertDatabaseMissing('media', ['id' => $oldMedia->id]);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($article->thumbnailMedia->path);
    }

    public function test_failed_thumbnail_replacement_keeps_old_media_and_cleans_up_new_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Storage Failure', 'slug' => 'storage-failure']);
        $oldPath = 'article/thumbnails/original-thumbnail.png';

        Storage::disk('public')->put($oldPath, 'original image');

        $oldMedia = Media::create([
            'user_id' => $admin->id,
            'disk' => 'public',
            'folder' => 'article/thumbnails',
            'path' => $oldPath,
            'original_name' => 'original-thumbnail.png',
            'mime_type' => 'image/png',
            'size' => 14,
            'usage' => 'thumbnail',
        ]);

        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Artikel Gagal Update',
            'slug' => 'artikel-gagal-update',
            'summary' => 'Ringkasan awal.',
            'content' => 'Konten awal.',
            'thumbnail_media_id' => $oldMedia->id,
            'status' => 'published',
        ]);

        DB::statement("
            CREATE TRIGGER fail_article_update
            BEFORE UPDATE ON articles
            BEGIN
                SELECT RAISE(ABORT, 'forced update failure');
            END
        ");

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->put(route('articles.update', $article), [
                'title' => 'Artikel Gagal Update Baru',
                'category_id' => $category->id,
                'summary' => 'Ringkasan baru.',
                'content' => 'Konten baru.',
                'status' => 'published',
                'thumbnail' => $this->fakePngUpload(),
                'quizzes' => $this->quizPayload(),
            ]);

            $this->fail('Database update should have failed.');
        } catch (QueryException) {
            // Expected: the controller must compensate for the stored replacement file.
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS fail_article_update');
        }

        $article->refresh();

        $this->assertSame($oldMedia->id, $article->thumbnail_media_id);
        $this->assertDatabaseHas('media', ['id' => $oldMedia->id]);
        $this->assertDatabaseCount('media', 1);
        Storage::disk('public')->assertExists($oldPath);
        $this->assertCount(1, Storage::disk('public')->allFiles('article/thumbnails'));
    }

    public function test_article_summary_has_a_maximum_length(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Summary', 'slug' => 'summary']);

        $response = $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Ringkasan Terlalu Panjang',
            'category_id' => $category->id,
            'summary' => str_repeat('a', 1001),
            'content' => 'Konten artikel.',
            'status' => 'draft',
            'thumbnail' => $this->fakePngUpload(),
            'quizzes' => $this->quizPayload(),
        ]);

        $response->assertSessionHasErrors('summary');
    }

    public function test_article_quiz_can_be_saved_and_rendered(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'Quiz', 'slug' => 'quiz']);

        $response = $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Artikel Dengan Quiz',
            'category_id' => $category->id,
            'summary' => 'Ringkasan artikel dengan quiz.',
            'content' => 'Isi artikel dengan quiz.',
            'quizzes' => [[
                'question' => 'Apa jawaban yang paling tepat?',
                'options' => ['Pilihan pertama', 'Pilihan kedua', 'Pilihan ketiga'],
                'correct_option' => 2,
            ]],
            'status' => 'published',
            'thumbnail' => $this->fakePngUpload(),
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $article = Article::with('normalizedQuiz.questions.options')->where('title', 'Artikel Dengan Quiz')->firstOrFail();
        $question = $article->normalizedQuiz->questions->first();
        $correctOption = $question->options->firstWhere('is_correct', true);

        $this->assertSame('Apa jawaban yang paling tepat?', $question->question);
        $this->assertSame('Pilihan ketiga', $correctOption->option_text);

        $detail = $this->actingAs($user)->get(route('articles.show', $article->slug));

        $detail->assertOk();
        $detail->assertSee('Apa jawaban yang paling tepat?');
        $detail->assertSee('Pilihan ketiga');
        $detail->assertSeeHtml('data-correct-option="'.$correctOption->id.'"');

        $attempt = $this->actingAs($user)->postJson(route('articles.quiz-attempts.store', $article), [
            'quiz_option_id' => $correctOption->id,
        ]);

        $attempt->assertOk();
        $attempt->assertJson([
            'correct' => true,
            'message' => 'Jawaban anda benar',
        ]);
        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'quiz_id' => $article->normalizedQuiz->id,
            'score' => 100,
        ]);
    }

    public function test_author_can_upload_article_content_media(): void
    {
        $author = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($author)->post(route('articles.media.store'), [
            'media' => $this->fakeGifUpload(),
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['location']);
        $this->assertStringContainsString('/uploads/artikel/content/', $response->json('location'));

        $path = public_path(parse_url($response->json('location'), PHP_URL_PATH));
        $this->assertFileExists($path);

        if (is_file($path)) {
            unlink($path);
        }
    }

    public function test_regular_user_cannot_upload_article_content_media(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post(route('articles.media.store'), [
            'media' => $this->fakeGifUpload(),
        ])->assertForbidden();
    }

    public function test_article_content_html_is_sanitized_before_save(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Security', 'slug' => 'security']);

        $response = $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Artikel Aman',
            'category_id' => $category->id,
            'summary' => 'Ringkasan artikel aman.',
            'content' => '<p onclick="alert(1)">Konten aman</p><script>alert(1)</script><figure><img src="http://localhost:8000/uploads/artikel/content/demo.gif" class="article-media article-media--center" style="width: 320px; background-image: url(javascript:alert(1))"></figure><img src="https://example.com/bad.gif">',
            'status' => 'published',
            'thumbnail' => $this->fakePngUpload(),
            'quizzes' => $this->quizPayload(),
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $article = Article::where('title', 'Artikel Aman')->firstOrFail();

        $this->assertStringContainsString('<p>Konten aman</p>', $article->content);
        $this->assertStringContainsString('uploads/artikel/content/demo.gif', $article->content);
        $this->assertStringContainsString('article-media--center', $article->content);
        $this->assertStringContainsString('width: 320px', $article->content);
        $this->assertStringNotContainsString('onclick', $article->content);
        $this->assertStringNotContainsString('<script', $article->content);
        $this->assertStringNotContainsString('example.com/bad.gif', $article->content);
        $this->assertStringNotContainsString('javascript:', $article->content);
    }

    public function test_admin_review_update_keeps_article_content_images_and_gifs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Review Media', 'slug' => 'review-media']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Draft Dengan GIF',
            'slug' => 'draft-dengan-gif',
            'summary' => 'Ringkasan draft dengan GIF.',
            'content' => '<p>Konten awal.</p>',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->put(route('articles.update', $article), [
            'title' => 'Draft Dengan GIF Direview',
            'category_id' => $category->id,
            'summary' => 'Ringkasan draft dengan GIF direview.',
            'content' => '<p>Konten hasil review.</p><figure><img src="http://localhost:8000/uploads/artikel/content/review.gif" class="article-media article-media--center"><figcaption>GIF review</figcaption></figure>',
            'status' => 'published',
            'quizzes' => $this->quizPayload(),
        ]);

        $response->assertSessionHasNoErrors();

        $article->refresh();

        $this->assertSame('published', $article->status);
        $this->assertStringContainsString('<img src="/uploads/artikel/content/review.gif"', $article->content);
        $this->assertStringContainsString('article-media--center', $article->content);
        $this->assertStringContainsString('<figcaption>GIF review</figcaption>', $article->content);
    }

    public function test_article_detail_renders_sanitized_rich_content(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Media Rich', 'slug' => 'media-rich']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Artikel Rich Media',
            'slug' => 'artikel-rich-media',
            'summary' => 'Ringkasan artikel rich media.',
            'content' => '<p>Konten rich.</p><figure><img src="http://localhost:8000/uploads/artikel/content/demo.gif" class="article-media article-media--right"><figcaption>Demo GIF</figcaption></figure>',
            'status' => 'published',
        ]);

        $response = $this->actingAs($user)->get(route('articles.show', $article->slug));

        $response->assertOk();
        $response->assertSeeHtml('<div class="article-body">');
        $response->assertSeeHtml('article-media--right');
        $response->assertSeeHtml('<figcaption>Demo GIF</figcaption>');
    }

    public function test_article_detail_renders_thumbnail_inside_article_content_card(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Thumbnail Detail', 'slug' => 'thumbnail-detail']);
        $path = 'article/thumbnails/detail.png';

        Storage::disk('public')->put($path, 'thumbnail');

        $media = Media::create([
            'user_id' => $author->id,
            'disk' => 'public',
            'folder' => 'article/thumbnails',
            'path' => $path,
            'original_name' => 'detail.png',
            'mime_type' => 'image/png',
            'size' => 9,
            'usage' => 'thumbnail',
        ]);

        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Thumbnail Dalam Artikel',
            'slug' => 'thumbnail-dalam-artikel',
            'summary' => 'Ringkasan thumbnail dalam artikel.',
            'content' => '<p>Isi utama artikel.</p>',
            'thumbnail_media_id' => $media->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($user)->get(route('articles.show', $article->slug));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Microlearning session',
            $media->url,
            'Isi utama artikel.',
        ], false);
        $response->assertSee('Thumbnail artikel Thumbnail Dalam Artikel.');
    }

    public function test_article_detail_uses_fallback_thumbnail_when_media_is_missing(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Fallback Detail', 'slug' => 'fallback-detail']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Artikel Tanpa Media Thumbnail',
            'slug' => 'artikel-tanpa-media-thumbnail',
            'summary' => 'Ringkasan artikel tanpa media thumbnail.',
            'content' => '<p>Isi artikel tanpa thumbnail unggahan.</p>',
            'status' => 'published',
        ]);

        $fallbacks = [
            'assets/img/microlearning-data-dashboard.png',
            'assets/img/microlearning-clean-code.png',
            'assets/img/microlearning-time-focus.png',
        ];
        $expectedFallback = $fallbacks[($article->id - 1) % count($fallbacks)];

        $this->actingAs($user)
            ->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($expectedFallback)
            ->assertSee('Thumbnail artikel Artikel Tanpa Media Thumbnail.');
    }

    public function test_article_detail_renders_images_from_article_content(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $author = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Media', 'slug' => 'media']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Artikel Dengan Media',
            'slug' => 'artikel-dengan-media',
            'summary' => 'Ringkasan artikel dengan media.',
            'content' => "Pembuka artikel.\n\n![Animasi belajar](https://example.com/belajar.gif)\n\nuploads/artikel/foto.png",
            'status' => 'published',
        ]);

        $response = $this->actingAs($user)->get(route('articles.show', $article->slug));

        $response->assertOk();
        $response->assertSee('Pembuka artikel.');
        $response->assertSeeHtml('<img src="https://example.com/belajar.gif" alt="Animasi belajar"');
        $response->assertSeeHtml('uploads/artikel/foto.png" alt="Gambar artikel"');
    }

    private function fakePngUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'thumb_');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        return new UploadedFile($path, 'thumbnail.png', 'image/png', null, true);
    }

    private function fakeGifUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'thumb_');

        file_put_contents($path, base64_decode(
            'R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='
        ));

        return new UploadedFile($path, 'thumbnail.gif', 'image/gif', null, true);
    }

    private function quizPayload(): array
    {
        return [[
            'question' => 'Apa langkah terbaik setelah membaca artikel ini?',
            'options' => [
                'Membaca ulang judulnya saja.',
                'Merangkum ide utama dan mencoba contoh sederhana.',
                'Langsung melewati materi berikutnya.',
            ],
            'correct_option' => 1,
        ]];
    }
}
