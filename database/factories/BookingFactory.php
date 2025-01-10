<?php

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'room_type_id' => RoomType::factory(),
            'total_price' => $this->faker->randomFloat(2, 100, 1000),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'guest_count' => $this->faker->numberBetween(1, 5),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
        ];
    }
}
