<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'summary_en', 'content_en']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_en', 200)->nullable()->after('title');
            $table->text('summary_en')->nullable()->after('summary');
            $table->longText('content_en')->nullable()->after('content');
        });
    }
};
