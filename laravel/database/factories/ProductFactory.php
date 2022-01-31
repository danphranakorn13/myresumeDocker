<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(3, false),
            'description' => $this->faker->text(),
            'price' => $this->faker->randomFloat(2, 100, 9000000),
            'image' => $this->faker->imageUrl(800, 600),
            'created_by_user' => $this->faker->numberBetween(1,100),
        ];
    }
}
