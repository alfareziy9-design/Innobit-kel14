<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_shows_reader_and_author_choices_with_reader_selected(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Pembaca')
            ->assertSee('Penulis')
            ->assertSee('name="role" value="user"', false)
            ->assertSee('name="role" value="author"', false)
            ->assertSee('value="user" class="mr-2 accent-lime-600" checked', false);
    }

    public function test_reader_can_register_with_a_general_email(): void
    {
        $this->post(route('register.store'), $this->registrationData([
            'email' => 'reader@example.com',
            'role' => 'user',
        ]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $reader = User::where('email', 'reader@example.com')->firstOrFail();

        $this->assertSame('user', $reader->role);
        $this->assertSame('user', $reader->roleName());
    }

    public function test_author_can_register_with_valid_system_information_student_emails(): void
    {
        foreach ([
            '20082010001@student.upnjatim.ac.id',
            '24082010145@student.upnjatim.ac.id',
            '26082010999@student.upnjatim.ac.id',
        ] as $index => $email) {
            $this->post(route('register.store'), $this->registrationData([
                'name' => "Author {$index}",
                'email' => $email,
                'role' => 'author',
            ]))
                ->assertSessionHasNoErrors()
                ->assertSessionHas('success');

            $author = User::where('email', $email)->firstOrFail();

            $this->assertSame('author', $author->role);
            $this->assertSame('author', $author->roleName());
            $this->assertTrue($author->isActive());
        }
    }

    public function test_author_registration_rejects_invalid_student_emails(): void
    {
        foreach ([
            '20082010000@student.upnjatim.ac.id',
            '26082011000@student.upnjatim.ac.id',
            '2408201014@student.upnjatim.ac.id',
            '24082010abc@student.upnjatim.ac.id',
            '24082010145@example.com',
        ] as $email) {
            $this->post(route('register.store'), $this->registrationData([
                'email' => $email,
                'role' => 'author',
            ]))->assertSessionHasErrors('email');

            $this->assertDatabaseMissing('users', ['email' => $email]);
        }
    }

    public function test_registration_rejects_an_unavailable_role(): void
    {
        $this->post(route('register.store'), $this->registrationData([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]))->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_author_registration_rejects_an_email_that_is_already_registered(): void
    {
        User::factory()->create([
            'email' => '24082010145@student.upnjatim.ac.id',
            'role' => 'author',
        ]);

        $this->post(route('register.store'), $this->registrationData([
            'email' => '24082010145@student.upnjatim.ac.id',
            'role' => 'author',
        ]))->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', '24082010145@student.upnjatim.ac.id')->count());
    }

    public function test_registered_users_are_redirected_based_on_their_role_after_login(): void
    {
        $this->post(route('register.store'), $this->registrationData([
            'email' => 'reader@example.com',
            'role' => 'user',
        ]));

        $this->post(route('login.store'), [
            'email' => 'reader@example.com',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->post(route('logout'));

        $this->post(route('register.store'), $this->registrationData([
            'email' => '24082010145@student.upnjatim.ac.id',
            'role' => 'author',
        ]));

        $this->post(route('login.store'), [
            'email' => '24082010145@student.upnjatim.ac.id',
            'password' => 'password',
        ])->assertRedirect(route('author.dashboard'));
    }

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Pengguna Baru',
            'email' => 'user@example.com',
            'role' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }
}
