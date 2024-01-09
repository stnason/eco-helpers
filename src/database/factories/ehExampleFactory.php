<?php

//namespace database\factories;
namespace ScottNason\EcoHelpers\database\factories;

use Illuminate\Support\Str;
use Illuminate\Support\Int;
use Illuminate\Database\Eloquent\Factories\Factory;

class ehExampleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            //'email_verified_at' => now(),
            //'remember_token' => Str::random(10),

            'active' => Int::random(1),
            'archived' => Int::random(1),

            'name' => fake()->name(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'zip' => fake()->zipCode(),
            'phone' => fake()->phone(),
            'email' => fake()->unique()->safeEmail(),
            'birthdate' => fake()->birthDate(),
            'title' => fake()->title(),
            'bio' => fake()->text(),

        ];
    }
}