<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('آزمایشگاه گرانول', false);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_services_page_loads(): void
    {
        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('خدمات و آزمایش‌ها', false);
    }

    public function test_about_page_loads(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('دربارهٔ', false);
    }

    public function test_contact_page_shows_form(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('فرم تماس', false);
        $response->assertSee('name="phone"', false);
    }

    public function test_contact_form_saves_message(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => 'کشاورز نمونه',
            'phone' => '09123456789',
            'email' => 'farmer@example.com',
            'message' => 'برای آنالیز خاک مزرعه تماس می‌گیرم.',
        ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'کشاورز نمونه',
            'phone' => '09123456789',
        ]);
    }

    public function test_contact_form_rejects_invalid_phone(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.submit'), [
            'name' => 'کشاورز نمونه',
            'phone' => '12345',
            'message' => 'پیام آزمایشی',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_rejects_missing_message(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.submit'), [
            'name' => 'کشاورز نمونه',
            'phone' => '09123456789',
        ]);

        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_pages_link_the_shared_stylesheet_with_font(): void
    {
        $response = $this->get('/');

        $response->assertSee('css/granollab.css', false);

        $css = file_get_contents(public_path('css/granollab.css'));
        $this->assertStringContainsString('Vazirmatn', $css);
    }
}
