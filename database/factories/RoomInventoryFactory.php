<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomInventory>
 */
class RoomInventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),  // 使用 RoomType 的工廠生成房間類型 ID
            'date' => $this->faker->date(),  // 隨機生成日期
            'total_rooms' => $this->faker->numberBetween(1, 100),  // 隨機生成總房間數
            'booked_rooms' => $this->faker->numberBetween(0, 50),  // 隨機生成已預訂的房間數
        ];
    }
}
