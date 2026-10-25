<?php

namespace Tests\Feature;

use App\Models\TestType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_index_lists_active_test_types(): void
    {
        TestType::factory()->create(['name' => 'آنالیز خاک آزمایشی', 'slug' => 'soil-test-x']);
        TestType::factory()->create(['name' => 'خدمت غیرفعال', 'slug' => 'inactive-x', 'is_active' => false]);

        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('آنالیز خاک آزمایشی', false);
        $response->assertDontSee('خدمت غیرفعال', false);
    }

    public function test_service_detail_page_shows_info(): void
    {
        $service = TestType::factory()->create([
            'name' => 'آنالیز آب آزمایشی',
            'slug' => 'water-test-x',
            'sample_type' => 'یک لیتر آب',
            'turnaround_text' => '۳ روز کاری',
        ]);

        $response = $this->get("/services/water-test-x");

        $response->assertStatus(200);
        $response->assertSee('آنالیز آب آزمایشی', false);
        $response->assertSee('یک لیتر آب', false);
        $response->assertSee('۳ روز کاری', false);
    }

    public function test_service_without_price_shows_call_badge(): void
    {
        $service = TestType::factory()->create(['slug' => 'no-price-x', 'price' => null]);

        $response = $this->get("/services/no-price-x");

        $response->assertStatus(200);
        $response->assertSee('تماس بگیرید', false);
    }

    public function test_service_with_price_shows_formatted_price(): void
    {
        TestType::factory()->create(['slug' => 'priced-x', 'price' => 1234567]);

        $response = $this->get("/services/priced-x");

        $response->assertStatus(200);
        $response->assertSee('1,234,567', false);
    }

    public function test_inactive_service_returns_404(): void
    {
        TestType::factory()->create(['slug' => 'hidden-x', 'is_active' => false]);

        $this->get('/services/hidden-x')->assertStatus(404);
    }

    public function test_seeder_creates_six_default_groups(): void
    {
        $this->seed(\Database\Seeders\TestTypeSeeder::class);

        $this->assertDatabaseCount('test_types', 6);
        $this->assertDatabaseHas('test_types', ['slug' => 'plant-pest-disease']);
        $this->assertDatabaseHas('test_types', ['slug' => 'seed-testing']);
    }
}
