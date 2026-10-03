<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Kasir Toko',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'username' => 'Kasir Toko',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_log_in_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'kasir@example.com',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'username' => 'kasir@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post('/login', [
            'username' => 'missing-user',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }
}