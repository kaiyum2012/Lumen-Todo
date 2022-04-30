<?php

namespace Database\Seeders;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;

class TodosTableSeeder extends Seeder
{

    public function run()
    {
        $users = User::all();

        if (!count($users)) {
            $users = User::factory()->count(5)->create();
        }

        $users->each(function ($user) {
            $todos = Todo::factory()->count(10)->make();
            $user->todos()->saveMany($todos);
        });
    }
}
