<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->default(null)->change();
        });

        DB::table('users')->where('photo', 'default.png')->update(['photo' => null]);
    }

    public function down(): void
    {
        DB::table('users')->whereNull('photo')->update(['photo' => 'default.png']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->default('default.png')->nullable(false)->change();
        });
    }
};
