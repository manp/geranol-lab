<?php

namespace Database\Factories;

use App\Models\TestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestTypeFactory extends Factory
{
    public function definition(): array
    {
        $slug = $this->faker->unique()->slug(2);

        return [
            'name' => 'آزمایش '.$this->faker->words(2, true),
            'slug' => $slug,
            'description_short' => 'شرح کوتاه آزمایش '.$slug,
            'description_full' => 'شرح کامل آزمایش '.$slug,
            'sample_type' => 'نمونهٔ آزمایش',
            'turnaround_text' => '۳ روز کاری',
            'price' => null,
            'is_active' => true,
        ];
    }
}
