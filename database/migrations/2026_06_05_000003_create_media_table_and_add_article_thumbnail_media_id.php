<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('disk', 50)->default('public');
            $table->string('folder', 120);
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->enum('usage', ['thumbnail', 'content'])->default('content');
            $table->timestamps();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('thumbnail_media_id')->nullable()->after('thumbnail')->constrained('media')->nullOnDelete()->cascadeOnUpdate();
        });

        DB::table('articles')
            ->whereNotNull('thumbnail')
            ->orderBy('id')
            ->chunkById(100, function ($articles): void {
                foreach ($articles as $article) {
                    $path = 'uploads/artikel/'.$article->thumbnail;
                    $fullPath = public_path($path);

                    $mediaId = DB::table('media')->insertGetId([
                        'user_id' => $article->author_id,
                        'disk' => 'public',
                        'folder' => 'uploads/artikel',
                        'path' => $path,
                        'original_name' => $article->thumbnail,
                        'mime_type' => is_file($fullPath) ? mime_content_type($fullPath) : null,
                        'size' => is_file($fullPath) ? filesize($fullPath) : null,
                        'usage' => 'thumbnail',
                        'created_at' => $article->created_at,
                        'updated_at' => $article->updated_at,
                    ]);

                    DB::table('articles')->where('id', $article->id)->update([
                        'thumbnail_media_id' => $mediaId,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('thumbnail_media_id');
        });

        Schema::dropIfExists('media');
    }
};
