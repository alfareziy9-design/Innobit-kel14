<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->timestamp('last_message_at')->nullable()->after('read_at')->index();
            $table->timestamp('user_read_at')->nullable()->after('last_message_at')->index();
        });

        Schema::create('contact_conversation_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_message_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('sender_type', 20);
            $table->text('message');
            $table->timestamps();

            $table->index(['contact_message_id', 'id']);
        });

        DB::table('contact_messages')
            ->orderBy('id')
            ->each(function (object $thread): void {
                DB::table('contact_conversation_messages')->insert([
                    'contact_message_id' => $thread->id,
                    'sender_id' => $thread->user_id,
                    'sender_type' => 'user',
                    'message' => $thread->message,
                    'created_at' => $thread->created_at,
                    'updated_at' => $thread->updated_at,
                ]);

                DB::table('contact_messages')
                    ->where('id', $thread->id)
                    ->update([
                        'last_message_at' => $thread->created_at,
                        'user_read_at' => $thread->created_at,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_conversation_messages');

        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropIndex(['last_message_at']);
            $table->dropIndex(['user_read_at']);
            $table->dropColumn(['last_message_at', 'user_read_at']);
        });
    }
};
