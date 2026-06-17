<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\ContactConversationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_login_before_sending_contact_message(): void
    {
        $this->post(route('contact.send'), [
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'message' => 'Saya ingin bertanya tentang materi.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_authenticated_contact_submission_creates_thread_and_first_message(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Akun',
            'email' => 'akun@example.com',
        ]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Contact Test Browser',
        ])->actingAs($user)->post(route('contact.send'), [
            'name' => 'Nama Form',
            'email' => 'form@example.com',
            'message' => 'Pesan dari user terdaftar.',
        ])->assertRedirect(route('messages.index'));

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $user->id,
            'name' => 'Nama Form',
            'email' => 'form@example.com',
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Contact Test Browser',
        ]);
        $this->assertDatabaseHas('contact_conversation_messages', [
            'sender_id' => $user->id,
            'sender_type' => 'user',
            'message' => 'Pesan dari user terdaftar.',
        ]);
    }

    public function test_contact_submission_validates_lengths_and_rejects_honeypot(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('contact'))->post(route('contact.send'), [
            'name' => str_repeat('a', 101),
            'email' => 'not-an-email',
            'message' => str_repeat('x', 5001),
            'website' => 'https://spam.example',
        ])->assertRedirect(route('contact'))
            ->assertSessionHasErrors(['name', 'email', 'message', 'website']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_route_is_rate_limited_after_five_requests_per_minute(): void
    {
        $user = User::factory()->create();
        $payload = [
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'message' => 'Pesan uji.',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->actingAs($user)->post(route('contact.send'), $payload)->assertRedirect(route('messages.index'));
        }

        $this->actingAs($user)->post(route('contact.send'), $payload)->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 5);
    }

    public function test_only_admin_can_access_contact_message_inbox(): void
    {
        $message = $this->createMessage();
        $nonAdmin = User::factory()->create(['role' => 'user']);

        $this->get(route('admin.messages.index'))->assertRedirect(route('login'));
        $this->actingAs($nonAdmin)->get(route('admin.messages.index'))->assertForbidden();
        $this->actingAs($nonAdmin)->get(route('admin.messages.show', $message))->assertForbidden();
        $this->actingAs($nonAdmin)->delete(route('admin.messages.destroy', $message))->assertForbidden();
    }

    public function test_admin_dashboard_shows_total_and_five_latest_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (range(1, 6) as $number) {
            $this->createMessage([
                'name' => "Pengirim {$number}",
                'created_at' => Carbon::parse("2026-06-0{$number} 10:00:00"),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('6 pesan tersimpan')
            ->assertSee('Pengirim 6')
            ->assertSee('Pengirim 2')
            ->assertDontSee('Pengirim 1');
    }

    public function test_admin_inbox_is_paginated_twenty_per_page_and_ordered_newest_first(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (range(1, 21) as $number) {
            $this->createMessage([
                'name' => "Pengirim {$number}",
                'created_at' => Carbon::parse('2026-06-01')->addMinutes($number),
            ]);
        }

        $firstPage = $this->actingAs($admin)->get(route('admin.messages.index'));
        $firstPage
            ->assertOk()
            ->assertSeeInOrder(['Pengirim 21', 'Pengirim 20'])
            ->assertSee('Pengirim 2')
            ->assertDontSee('>Pengirim 1</p>', false);

        $this->actingAs($admin)
            ->get(route('admin.messages.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Pengirim 1')
            ->assertDontSee('>Pengirim 21</p>', false);
    }

    public function test_admin_can_view_escaped_message_and_delete_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $message = $this->createMessage([
            'email' => 'sender@example.com',
            'message' => '<script>alert("xss")</script>',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.messages.show', $message))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertDontSee('mailto:sender@example.com', false);

        $this->actingAs($admin)
            ->delete(route('admin.messages.destroy', $message))
            ->assertRedirect(route('admin.messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
        $this->assertDatabaseMissing('contact_conversation_messages', ['contact_message_id' => $message->id]);
    }

    public function test_user_can_only_view_and_reply_to_own_threads(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $thread = $this->createMessage(['user_id' => $owner->id]);
        $this->createConversationMessage($thread, $owner, 'user', 'Pesan pemilik.');

        $this->actingAs($owner)
            ->get(route('messages.show', $thread))
            ->assertOk()
            ->assertSee('Pesan pemilik.');

        $this->actingAs($otherUser)->get(route('messages.show', $thread))->assertForbidden();
        $this->actingAs($otherUser)
            ->postJson(route('messages.reply', $thread), ['message' => 'Tidak boleh.'])
            ->assertForbidden();
    }

    public function test_admin_and_user_can_reply_and_poll_new_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $thread = $this->createMessage([
            'user_id' => $user->id,
            'read_at' => now(),
            'user_read_at' => now(),
        ]);
        $firstMessage = $this->createConversationMessage($thread, $user, 'user', 'Pertanyaan awal.');

        $adminReply = $this->actingAs($admin)
            ->postJson(route('admin.messages.reply', $thread), ['message' => 'Balasan admin.'])
            ->assertCreated()
            ->assertJsonPath('message.sender_name', 'Admin InnoBit')
            ->json('message');

        $this->assertNull($thread->fresh()->user_read_at);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'message.replied',
            'subject_id' => $thread->id,
        ]);

        $this->actingAs($user)
            ->getJson(route('messages.updates', ['contactMessage' => $thread, 'after_id' => $firstMessage->id]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.id', $adminReply['id']);

        $this->assertNotNull($thread->fresh()->user_read_at);

        $userReply = $this->actingAs($user)
            ->postJson(route('messages.reply', $thread), ['message' => 'Terima kasih.'])
            ->assertCreated()
            ->json('message');

        $this->assertNull($thread->fresh()->read_at);

        $this->actingAs($admin)
            ->getJson(route('admin.messages.updates', ['contactMessage' => $thread, 'after_id' => $adminReply['id']]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.id', $userReply['id']);

        $this->assertNotNull($thread->fresh()->read_at);
    }

    public function test_regular_form_reply_redirects_back_instead_of_showing_json_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $thread = $this->createMessage(['user_id' => $user->id]);

        $this->actingAs($admin)
            ->post(route('admin.messages.reply', $thread), ['message' => 'Balasan tanpa JavaScript.'])
            ->assertRedirect(route('admin.messages.show', $thread))
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('messages.reply', $thread), ['message' => 'Jawaban tanpa JavaScript.'])
            ->assertRedirect(route('messages.show', $thread))
            ->assertSessionHas('success');
    }

    public function test_admin_message_access_is_focused_on_admin_inbox(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $thread = $this->createMessage(['user_id' => $user->id]);

        $this->actingAs($admin)
            ->get(route('messages.index'))
            ->assertRedirect(route('admin.messages.index'));

        $this->actingAs($admin)
            ->get(route('messages.show', $thread))
            ->assertRedirect(route('admin.messages.show', $thread));

        $this->actingAs($admin)
            ->get(route('contact'))
            ->assertOk()
            ->assertSee('Buka Inbox Admin')
            ->assertDontSee('Lihat Pesan Saya');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Inbox Pesan')
            ->assertDontSee('Pesan Saya');
    }

    public function test_legacy_guest_thread_is_read_only_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $thread = $this->createMessage(['user_id' => null]);

        $this->actingAs($admin)
            ->get(route('admin.messages.show', $thread))
            ->assertOk()
            ->assertSee('hanya dapat dibaca');

        $this->actingAs($admin)
            ->postJson(route('admin.messages.reply', $thread), ['message' => 'Tidak terkirim.'])
            ->assertStatus(422);
    }

    private function createMessage(array $attributes = []): ContactMessage
    {
        $message = new ContactMessage;
        $message->forceFill(array_merge([
            'name' => 'Pengirim',
            'email' => 'sender@example.com',
            'message' => 'Isi pesan.',
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Test Browser',
        ], $attributes));
        $message->save();

        return $message;
    }

    private function createConversationMessage(
        ContactMessage $thread,
        ?User $sender,
        string $senderType,
        string $message
    ): ContactConversationMessage {
        $conversationMessage = $thread->conversationMessages()->create([
            'sender_id' => $sender?->id,
            'sender_type' => $senderType,
            'message' => $message,
        ]);

        $thread->update(['last_message_at' => $conversationMessage->created_at]);

        return $conversationMessage;
    }
}
