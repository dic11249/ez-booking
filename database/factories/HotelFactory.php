<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hotel>
 */
class HotelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'country' => $this->faker->country,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'total_rooms' => $this->faker->numberBetween(10, 200),
            'rating' => $this->faker->optional()->randomFloat(2, 1, 5),
            'is_active' => $this->faker->boolean,
        ];
    }
}
