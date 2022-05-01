<?php

use App\Models\Todo;
use App\Models\User;
use App\Traits\ApiTokenGenerator;
use Laravel\Lumen\Testing\DatabaseMigrations;

class TodoTest extends TestCase
{
    use DatabaseMigrations, ApiTokenGenerator;

    private $validaData = ['email' => 'abdul@todo.com', 'password' => 'password'];
    private $invalidData = ['email' => '', 'password' => ''];
    private $existingUser = ['email' => 'abdul@todo.com', 'password' => 'password'];

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
        // Register
        $this->post(route('register'), $this->validaData)
            ->seeStatusCode(201);
        $this->assertNotEmpty($this->response->json('token'), 'token key is not present in response');

        $this->post(route('register'), $this->existingUser)
            ->seeStatusCode(422);
        $this->assertNotEmpty($this->response->json('email'));

        // Login
        $this->post(route('login'), $this->validaData)
            ->assertResponseOk();
        $this->assertNotEmpty($this->response->json('token'), 'token key is not present in response');
        $token = $this->response->json('token');

        // Get Auth User
        $headers = ['Authorization' => 'bearer '.$token];
        $this->get(route('auth.user'), $headers)->assertResponseOk();
        $this->assertTrue($this->response->json('id') > 0 && is_int($this->response->json('id')));
    }

    public function testTodosFeature()
    {
        // Truncate tables
        Todo::query()->truncate();
        $this->assertTrue(Todo::all()->count() === 0, 'todo table is not truncated completely');

        User::query()->truncate();
        $this->assertTrue(User::all()->count() === 0, 'Users table is not truncated completely');

        $user = User::factory()->create(array_merge($this->validaData, ['token' => $this->generateApiToken()]));

        $headers = ['Authorization' => 'bearer '.$user->token];
        $payload = ['note' => 'here is the first note'];
        $this->post(route('todo.create'), $payload, $headers)->seeStatusCode(201);
        $this->seeJsonContains($payload);

        $this->get(route('todo.list'), $headers)->assertResponseOk();
        $this->response->assertJsonCount(1);

        $note = $this->response[0];
        // valid note
        $this->get(route('todo.show', ['id' => $note['id']]), $headers)
            ->assertResponseStatus(200);

        // invalid note show
        $this->get(route('todo.show', ['id' => 2]), $headers)->assertResponseStatus(202);

        // update note
        $updatedPayload = ['note' => 'This note is changed!'];
        $this->patch(route('todo.update', ['id' => $note['id']]), $updatedPayload)
            ->assertResponseStatus(201);
        $this->assertEquals($updatedPayload['note'], $this->response->json('note'));
        // invalid note update
        $this->patch(route('todo.update', ['id' => 2]), $updatedPayload)
            ->assertResponseStatus(202);

        // mark note complete
        $this->post(route('todo.mark-complete', ['id' => $note['id']]))->assertResponseStatus(201);
        $this->assertNotNull($this->response->json('complete_at'));
        // mark invalid note as complete
        $this->post(route('todo.mark-complete', ['id' => 2]))->assertResponseStatus(202);

        // mark note incomplete
        $this->post(route('todo.mark-incomplete', ['id' => $note['id']]))->assertResponseStatus(201);
        $this->assertNull($this->response->json('complete_at'));
        // mark invalid note as incomplete
        $this->post(route('todo.mark-complete', ['id' => 2]))->assertResponseStatus(202);

        // test unknown todo
        $this->get(route('todo.show', ['id' => 1000]))->assertResponseStatus(202);
        $this->get(route('todo.show', ['id' => 'asdasd']))->assertResponseStatus(404);
        $this->patch(route('todo.update', ['id' => 1000]), $updatedPayload)->assertResponseStatus(202);
        $this->patch(route('todo.update', ['id' => 'asdaa']), $updatedPayload)->assertResponseStatus(404);
        $this->post(route('todo.mark-complete', ['id' => 1000]))->assertResponseStatus(202);
        $this->post(route('todo.mark-complete', ['id' => 'wqdd']))->assertResponseStatus(404);
        $this->post(route('todo.mark-incomplete', ['id' => 1000]))->assertResponseStatus(202);
        $this->post(route('todo.mark-incomplete', ['id' => 'addadd']))->assertResponseStatus(404);

        // delete note
        $this->delete(route('todo.delete', ['id' => $note['id']]))
            ->assertResponseStatus(204);
        // delete invalid note
        $this->delete(route('todo.delete', ['id' => $note['id']]))
            ->assertResponseStatus(202);

        $this->delete(route('todo.delete', ['id' => 1000]))->assertResponseStatus(202);
        $this->delete(route('todo.delete', ['id' => 'asdad']))->assertResponseStatus(404);
    }
}
