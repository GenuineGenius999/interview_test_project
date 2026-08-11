<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeededAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_email_with_line_breaks(): void
    {
        $this->post(route('login.store'), [
            'email' => "manager@northwind.test\r\nBcc: attacker@example.test",
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_every_seeded_account_can_log_in(): void
    {
        $this->seed();

        $emails = [
            'manager@northwind.test',
            'member@northwind.test',
            'manager@contoso.test',
            'member@contoso.test',
        ];

        foreach ($emails as $email) {
            $this->post(route('login.store'), [
                'email' => $email,
                'password' => 'password',
            ])->assertRedirect(route('tickets.index'));

            $this->assertAuthenticatedAs(
                \App\Models\User::query()->where('email', $email)->firstOrFail()
            );

            auth()->logout();
        }
    }
}
