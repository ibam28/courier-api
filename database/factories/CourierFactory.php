<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('KRR###'),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'vehicle_type' => fake()->randomElement([
                'motor',
                'mobil',
                'van',
                'truck',
            ]),
            'vehicle_plate' => fake()->bothify('B #### ???'),
            'level' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement([
                'active',
                'inactive',
                'suspended',
            ]),
            'joined_at' => fake()->dateTimeBetween('-3 years', 'now'),
        ];
    }
}
