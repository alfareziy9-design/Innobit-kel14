<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('label', 100);
            $table->timestamps();
        });

        foreach ([
            ['name' => 'admin', 'label' => 'Administrator'],
            ['name' => 'author', 'label' => 'Author'],
            ['name' => 'user', 'label' => 'User'],
        ] as $role) {
            DB::table('roles')->insert([
                ...$role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained()->nullOnDelete()->cascadeOnUpdate();
        });

        $roles = DB::table('roles')->pluck('id', 'name');

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) use ($roles): void {
            foreach ($users as $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['role_id' => $roles[$user->role] ?? $roles['user']]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
