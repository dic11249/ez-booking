<?php

namespace Database\Factories;

use App\Enums\AmenityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word, 
            'type' => $this->faker->randomElement(array_column(AmenityType::cases(), 'value')), 
            'description' => $this->faker->sentence, 
        ];
    }
}
