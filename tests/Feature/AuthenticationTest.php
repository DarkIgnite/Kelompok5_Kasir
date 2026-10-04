<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang di Sistem KasirAja');
    }

    public function test_user_can_log_in_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'kasir1',
            'password' => 'password',
            'status' => 'aktif',
        ]);

        $response = $this->post('/login', [
            'username' => 'kasir1',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::factory()->create([
            'username' => 'kasir_nonaktif',
            'password' => 'password',
            'status' => 'nonaktif',
        ]);

        $response = $this->post('/login', [
            'username' => 'kasir_nonaktif',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
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