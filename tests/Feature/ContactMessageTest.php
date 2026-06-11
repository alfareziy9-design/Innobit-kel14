<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_contact_submission_is_stored_with_request_metadata(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Contact Test Browser',
        ])->post(route('contact.send'), [
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'message' => 'Saya ingin bertanya tentang materi.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => null,
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'message' => 'Saya ingin bertanya tentang materi.',
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Contact Test Browser',
        ]);
    }

    public function test_authenticated_contact_submission_keeps_sender_snapshot_and_user_link(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Akun',
            'email' => 'akun@example.com',
        ]);

        $this->actingAs($user)->post(route('contact.send'), [
            'name' => 'Nama Form',
            'email' => 'form@example.com',
            'message' => 'Pesan dari user terdaftar.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $user->id,
            'name' => 'Nama Form',
            'email' => 'form@example.com',
        ]);
    }

    public function test_contact_submission_validates_lengths_and_rejects_honeypot(): void
    {
        $this->from(route('contact'))->post(route('contact.send'), [
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
        $payload = [
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'message' => 'Pesan uji.',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('contact.send'), $payload)->assertRedirect();
        }

        $this->post(route('contact.send'), $payload)->assertTooManyRequests();
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
            ->assertSee('mailto:sender@example.com', false);

        $this->actingAs($admin)
            ->delete(route('admin.messages.destroy', $message))
            ->assertRedirect(route('admin.messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
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
}
