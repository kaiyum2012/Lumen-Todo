<?php

use App\Models\Todo;
use App\Models\User;
use App\Traits\ApiTokenGenerator;
use Laravel\Lumen\Testing\DatabaseMigrations;

class TodoTest extends TestCase
{
    use DatabaseMigrations, ApiTokenGenerator;

    private array $data = ['email' => 'abdul@todo.com', 'password' => 'password'];

    protected function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        if (!count($users)) {
            $users = User::factory()->count(3)->create();
        }

        $users->each(function ($user) {
            $todos = Todo::factory()->count(5)->make();
            $user->todos()->saveMany($todos);
        });

    }

    public function testUserWithTodosAvailable()
    {
        $users = User::all();
        foreach ($users as $user) {
            $this->seeInDatabase('users', ['email' => $user->email, 'password' => $user->password]);

            $this->assertTrue(count($user->todos) === 5, 'todos are not persisted properly');
        }
    }


    public function testAuthRoutes()
    {

        $this->post('/api/signup', $this->data)
            ->seeStatusCode(201);
        $this->assertNotEmpty($this->response->json('token'), 'token key is not present in response');

        $this->post('/api/login', $this->data)
            ->assertResponseOk();
        $this->assertNotEmpty($this->response->json('token'), 'token key is not present in response');
        $token = $this->response->json('token');

        $headers = ['Authorization' => 'bearer '.$token];
        $this->get('/api/user', $headers)->assertResponseOk();
        $this->assertTrue($this->response->json('id') > 0 && is_int($this->response->json('id')));
    }

    public function testTodosFeature()
    {
        User::query()->delete();
        $this->assertTrue(User::all()->count() === 0, 'Users table is not truncated completely');

        Todo::query()->delete();
        $this->assertTrue(Todo::all()->count() === 0, 'todo table is not truncated completely');

        $user = User::factory()->create(array_merge($this->data, ['token' => $this->generateApiToken()]));

        $headers = ['Authorization' => 'bearer '.$user->token];
        $payload = ['note' => 'here is the first note'];
        $this->post('/api/todo', $payload, $headers)->seeStatusCode(201);
        $this->seeJsonContains($payload);

        $this->get('/api/todo', $headers)->assertResponseOk();
        $this->response->assertJsonCount(1);

        $note = $this->response[0];
        // valid note
        $this->get('api/todo/'.$note['id'], $headers)->assertResponseStatus(200);

        // invalid note
        $this->get('api/todo/2', $headers)->assertResponseStatus(202);


    }
}
