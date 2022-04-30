<?php

namespace Database\Factories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{

    protected $model = Todo::class;

    public function definition()
    {
        return [
            'note' => $this->faker->sentence,
            'complete_at' => null,
        ];
    }
}
