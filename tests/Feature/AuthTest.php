<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_with_mobile_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'مشتری نمونه',
            'mobile' => '09123456789',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.placeholder'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['mobile' => '09123456789']);

        $user = User::where('mobile', '09123456789')->first();
        $this->assertNotNull($user);
        $this->assertNotSame('password123', $user->password);
    }

    public function test_registration_rejects_invalid_mobile(): void
    {
        $response = $this->from(route('register'))->post('/register', [
            'name' => 'مشتری نمونه',
            'mobile' => '12345',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('mobile');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_duplicate_mobile(): void
    {
        User::factory()->create(['mobile' => '09123456789']);

        $response = $this->post('/register', [
            'name' => 'مشتری دوم',
            'mobile' => '09123456789',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('mobile');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_can_login_with_mobile_and_password(): void
    {
        $user = User::factory()->create([
            'mobile' => '09121234567',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'mobile' => '09121234567',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.placeholder'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['mobile' => '09121234567']);

        $response = $this->post('/login', [
            'mobile' => '09121234567',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('mobile');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_is_visible_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.placeholder'));

        $response->assertOk()->assertSee($user->name);
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard.placeholder'));
    }
}
