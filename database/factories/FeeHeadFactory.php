<?php

namespace Database\Factories;

use App\Models\FeeHead;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeHeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeeHead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'amount' => $this->faker->numberBetween(100, 1000),
            'semester_id' => \App\Models\Semester::factory(),
            'fee_type' => 'Regular',
            'is_discountable' => false,
        ];
    }
}
