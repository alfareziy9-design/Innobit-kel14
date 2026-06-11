<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::table('article_collections', function (Blueprint $table) {
            $table->foreignId('collection_id')
                ->nullable()
                ->after('user_id')
                ->constrained('learning_collections')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        DB::table('article_collections')
            ->select('user_id')
            ->distinct()
            ->orderBy('user_id')
            ->get()
            ->each(function ($row): void {
                $collectionId = DB::table('learning_collections')->insertGetId([
                    'user_id' => $row->user_id,
                    'name' => 'Koleksi Utama',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('article_collections')
                    ->where('user_id', $row->user_id)
                    ->update(['collection_id' => $collectionId]);
            });
    }

    public function down(): void
    {
        Schema::table('article_collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_id');
        });

        Schema::dropIfExists('learning_collections');
    }
};
