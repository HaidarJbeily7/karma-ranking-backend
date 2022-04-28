<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Image;


class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $models = Image::class;

    public function definition()
    {
        $choices = ['h.png', 't.png', 'j.png'];

        return [
            'url' => env('ASSETS_URL') . $choices[$this->faker->unique()->numberBetween(0, count($choices)-1)],
        ];
    }
}
