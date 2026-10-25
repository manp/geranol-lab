<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_customer_role(): void
    {
        $this->post('/register', [
            'name' => 'مشتری تازه',
            'mobile' => '09123456789',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['mobile' => '09123456789', 'role' => 'customer']);
    }

    public function test_customer_gets_403_on_admin_panel(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($customer)->get(route('admin.users.create'))
            ->assertForbidden();
    }

    public function test_employee_can_access_admin_panel(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login_not_403(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_create_employee(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'کارمند تازه',
            'mobile' => '09121112233',
            'password' => 'password123',
            'role' => 'employee',
        ]);

        $this->assertDatabaseHas('users', ['mobile' => '09121112233', 'role' => 'employee']);
    }

    public function test_guest_cannot_store_users(): void
    {
        $this->post(route('admin.users.store'), [
            'name' => 'نفوذگر',
            'mobile' => '09129998877',
            'password' => 'password123',
            'role' => 'admin',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['mobile' => '09129998877']);
    }

    public function test_role_change_takes_effect(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($admin)->put(route('admin.users.update', $employee), [
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', ['id' => $employee->id, 'role' => 'admin']);

        // نقش جدید باید بلافاصله اثر بگذارد
        $this->actingAs($employee->refresh())->get(route('admin.users.create'))
            ->assertOk();
    }

    public function test_deactivated_employee_is_blocked_by_gate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($admin)->put(route('admin.users.update', $employee), [
            'role' => 'employee',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('users', ['id' => $employee->id, 'is_active' => false]);

        $this->actingAs($employee->refresh())->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'role' => 'employee',
            'is_active' => true,
        ])->assertForbidden();
    }
}
