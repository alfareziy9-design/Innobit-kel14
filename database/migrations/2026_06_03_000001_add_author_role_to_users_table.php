<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->rebuildUsersTable(['admin', 'author', 'user']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'author')->update(['role' => 'user']);

        $this->rebuildUsersTable(['admin', 'user']);
    }

    private function rebuildUsersTable(array $roles): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('users_new', function (Blueprint $table) use ($roles): void {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('photo')->default('default.png');
            $table->enum('role', $roles)->default('user');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users_new')->insert([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'password' => $user->password,
                        'photo' => $user->photo,
                        'role' => $user->role,
                        'remember_token' => $user->remember_token,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]);
                }
            });

        Schema::drop('users');
        Schema::rename('users_new', 'users');

        Schema::enableForeignKeyConstraints();
    }
};
