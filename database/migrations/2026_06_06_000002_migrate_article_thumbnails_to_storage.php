<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    private array $legacyFilesToDelete = [];

    public function up(): void
    {
        if (DB::table('articles')->whereNull('summary')->exists()) {
            throw new RuntimeException('Article summaries must be populated before making the column required.');
        }

        $this->createMissingMediaRecords();
        $this->moveLegacyThumbnailsToStorage();

        Schema::table('articles', function (Blueprint $table) {
            $table->text('summary')->nullable(false)->change();
            $table->dropColumn('thumbnail');
        });

        foreach ($this->legacyFilesToDelete as $path) {
            File::delete($path);
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('thumbnail')->nullable()->after('content');
            $table->text('summary')->nullable()->change();
        });

        DB::table('articles')
            ->leftJoin('media', 'media.id', '=', 'articles.thumbnail_media_id')
            ->whereNotNull('media.id')
            ->select('articles.id', 'media.id as media_id', 'media.disk', 'media.path')
            ->orderBy('articles.id')
            ->each(function ($article): void {
                $legacyPath = 'uploads/artikel/'.basename($article->path);
                $legacyFullPath = public_path($legacyPath);

                File::ensureDirectoryExists(dirname($legacyFullPath));

                $stream = Storage::disk($article->disk)->readStream($article->path);

                try {
                    if ($stream === false || File::put($legacyFullPath, $stream) === false) {
                        throw new RuntimeException("Unable to restore thumbnail: {$article->path}");
                    }
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['thumbnail' => basename($legacyPath)]);

                DB::table('media')->where('id', $article->media_id)->update([
                    'folder' => 'uploads/artikel',
                    'path' => $legacyPath,
                    'updated_at' => now(),
                ]);

                Storage::disk($article->disk)->delete($article->path);
            });
    }

    private function createMissingMediaRecords(): void
    {
        DB::table('articles')
            ->whereNotNull('thumbnail')
            ->whereNull('thumbnail_media_id')
            ->orderBy('id')
            ->each(function ($article): void {
                $legacyPath = 'uploads/artikel/'.$article->thumbnail;
                $fullPath = public_path($legacyPath);

                if (! File::isFile($fullPath)) {
                    throw new RuntimeException("Legacy thumbnail is missing: {$legacyPath}");
                }

                $mediaId = DB::table('media')->insertGetId([
                    'user_id' => $article->author_id,
                    'disk' => 'public',
                    'folder' => 'uploads/artikel',
                    'path' => $legacyPath,
                    'original_name' => $article->thumbnail,
                    'mime_type' => File::mimeType($fullPath),
                    'size' => File::size($fullPath),
                    'usage' => 'thumbnail',
                    'created_at' => $article->created_at,
                    'updated_at' => $article->updated_at,
                ]);

                DB::table('articles')
                    ->where('id', $article->id)
                    ->update(['thumbnail_media_id' => $mediaId]);
            });
    }

    private function moveLegacyThumbnailsToStorage(): void
    {
        DB::table('media')
            ->where('usage', 'thumbnail')
            ->where('disk', 'public')
            ->where('path', 'like', 'uploads/artikel/%')
            ->orderBy('id')
            ->each(function ($media): void {
                $source = public_path($media->path);
                $target = 'article/thumbnails/'.basename($media->path);

                if (! File::isFile($source)) {
                    throw new RuntimeException("Legacy thumbnail is missing: {$media->path}");
                }

                $stream = fopen($source, 'rb');

                try {
                    if ($stream === false || ! Storage::disk('public')->put($target, $stream)) {
                        throw new RuntimeException("Unable to migrate thumbnail: {$media->path}");
                    }
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                DB::table('media')->where('id', $media->id)->update([
                    'folder' => 'article/thumbnails',
                    'path' => $target,
                    'updated_at' => now(),
                ]);

                $this->legacyFilesToDelete[] = $source;
            });
    }
};
