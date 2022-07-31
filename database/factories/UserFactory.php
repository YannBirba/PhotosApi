<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group_id' => $this->faker->numberBetween(1, 500),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => '$2y$10$slt838cxJ3RKQkHGhByBruq2gnFkMRqLL03CzfR6t0f/PqWpQskju',
            'is_admin' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(75),
        ];
    }
}
