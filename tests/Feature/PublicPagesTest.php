<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
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

    public function test_contact_page_loads(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('تماس با ما', false);
    }

    public function test_pages_link_the_shared_stylesheet_with_font(): void
    {
        $response = $this->get('/');

        $response->assertSee('css/granollab.css', false);

        $css = file_get_contents(public_path('css/granollab.css'));
        $this->assertStringContainsString('Vazirmatn', $css);
    }
}
