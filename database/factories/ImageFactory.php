<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'event_id' => $this->faker->numberBetween(1, 1000),
            'name' => $this->faker->text(20),
            'path' => $this->faker->imageUrl(),
            'alt' => $this->faker->text(75),
            'title' => $this->faker->text(75),
            'extension' => 'jpg',
        ];
    }
}
