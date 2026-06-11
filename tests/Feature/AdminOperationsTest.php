<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_user_role_and_action_is_audited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $authorRole = Role::where('name', 'author')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $user), ['role_id' => $authorRole->id])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('author', $user->roleName());
        $this->assertSame('author', $user->role);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.role_updated',
            'subject_id' => $user->id,
        ]);
    }

    public function test_admin_cannot_demote_or_suspend_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userRole = Role::where('name', 'user')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role_id' => $userRole->id])
            ->assertStatus(422);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $admin), ['account_status' => 'suspended'])
            ->assertStatus(422);

        $admin->refresh();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'suspended@example.com',
            'account_status' => 'suspended',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_opening_message_marks_it_read_and_admin_can_mark_it_unread(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $message = ContactMessage::create([
            'name' => 'Rina',
            'email' => 'rina@example.com',
            'message' => 'Mohon bantuan.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.messages.show', $message))
            ->assertOk();

        $this->assertNotNull($message->fresh()->read_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'message.read',
            'subject_id' => $message->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.messages.read', $message), ['is_read' => false])
            ->assertSessionHasNoErrors();

        $this->assertNull($message->fresh()->read_at);
    }

    public function test_review_decision_cannot_be_applied_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Review', 'slug' => 'review']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Review Sekali',
            'slug' => 'review-sekali',
            'summary' => 'Ringkasan.',
            'content' => 'Konten.',
            'status' => 'review',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.articles.approve', $article))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.articles.reject', $article), ['note' => 'Terlambat.'])
            ->assertStatus(422);

        $this->assertDatabaseCount('article_reviews', 1);
        $this->assertSame('published', $article->fresh()->status);
    }

    public function test_only_admin_can_access_user_management_and_activity_log(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.activity.index'))->assertForbidden();
    }
}
