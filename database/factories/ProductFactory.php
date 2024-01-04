<?php

namespace Database\Factories;

use App\Enum\ActiveEnum;
use App\Models\InstagramFollower;
use App\Models\InstagramPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price' => $this->faker->numberBetween(),
            'productable_type' => $this->faker->randomElement([InstagramFollower::class, InstagramPage::class]),
            'productable_id' => $this->faker->randomElement([InstagramFollower::factory(), InstagramPage::factory()]),
            'quantity' => $this->faker->numberBetween(1,10),
            'is_active' => $this->faker->randomElement([ActiveEnum::ACTIVE->value,ActiveEnum::INACTIVE->value])
        ];
    }
}
