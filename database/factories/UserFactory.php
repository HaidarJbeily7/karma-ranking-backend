<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Image;
use App\Models\User;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = User::class;

    public function definition()
    {
        $images = Image::all()->toArray();
        $key = array_rand($images);

        return [
            'username' => $this->faker->name(),
            'karma_score' => mt_rand(0, 10000000),
            'image_id' => $images[$key]['id']
        ];
    }


}
