<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory()->create()->id,
            'name' => $this->faker->word,
            'capacity' => $this->faker->numberBetween(1, 4),
            'base_price' => $this->faker->randomFloat(2, 50, 500),
            'description' => $this->faker->optional()->sentence,
            'amenities' => $this->faker->optional()->words(5),
        ];
    }
}
