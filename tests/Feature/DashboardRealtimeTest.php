<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DashboardRealtimeTest extends TestCase
{
    public function test_guest_is_redirected_when_accessing_dashboard_data(): void
    {
        $response = $this->get('/dashboard/data');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard_page(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Penjualan & Inventaris', false);
        $response->assertSee('Real-Time');
        $response->assertSee('Simulasi Kasir');
    }

    public function test_authenticated_user_can_fetch_realtime_metrics_json(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->getJson('/dashboard/data');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stats' => [
                'total_penjualan',
                'total_penjualan_formatted',
                'persen_penjualan',
                'total_transaksi',
                'rata_rata_nota',
                'total_terjual',
                'peringatan_stok',
            ],
            'monthlySales',
            'chartPills',
            'topCategories',
            'restockAlerts',
            'categoryMargins',
            'slowMoving',
            'shoeCatalog',
            'timestamp',
        ]);
    }

    public function test_authenticated_user_can_simulate_cashier_sale(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->postJson('/dashboard/simulate-sale');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'sale',
            'data',
        ]);
    }
}
