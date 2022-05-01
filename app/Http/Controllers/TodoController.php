<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TodoController extends Controller
{
    private const ACTION_COMPLETE = 1;
    private const ACTION_INCOMPLETE = 0;

    /**
     * @return JsonResponse
     */
    public final function index(): JsonResponse
    {
//        Method 1 - Long way
//        return Todo::query()->where('user_id', '=', Auth::user()->getAuthIdentifier())->get();
//        Methods 2 :)
        return response()->json(Auth::user()->todos()->get());
    }


    /**
     * @throws ValidationException
     */
    public final function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->validationRules());

        $todo = Todo::query()->make(['note' => $request->get('note')]);
        $response = Auth::user()->todos()->save($todo);

        return response()->json($response,201);
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public final function get(int $id): JsonResponse
    {
        try {
            $note = $this->getNote($id);
        } catch (ModelNotFoundException $exception) {
            if (env('APP_ENV') === 'production') {
                return response()->json(['message' => 'note not found'], 202);
            } else {
                return response()->json(['message' => $exception->getMessage()], 202);
            }
        }

        return response()->json($note);
    }

    /**
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public final function update(int $id, Request $request): JsonResponse
    {
        $this->validate($request, $this->validationRules());

        try {
            $note = $this->getNote($id);
        } catch (ModelNotFoundException $exception) {
            if (env('APP_ENV') === 'production') {
                return response()->json(['message' => 'note not found'], 202);
            } else {
                return response()->json(['message' => $exception->getMessage()], 202);
            }
        }

        $note->fill($request->only('note'));
        $note->save();

        return response()->json($note->refresh(), 201);
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public final function destroy(int $id): JsonResponse
    {
        try {
            $note = $this->getNote($id);
        } catch (ModelNotFoundException $exception) {
            if (env('APP_ENV') === 'production') {
                return response()->json(['message' => 'note not found'], 202);
            } else {
                return response()->json(['message' => $exception->getMessage()], 202);
            }
        }

        $note->delete();
        return response()->json('', 204);
    }

    /**
     * @return JsonResponse
     */
    public final function listForArbitraryUser(): JsonResponse
    {
        $user = User::query()->inRandomOrder()->limit(1);
        if (!empty($user)) {
            $todos = $user->with('todos')->get();
            return response()->json($todos);
        } else {
            return response()->json(['message' => 'No user found in system'], 202);
        }
    }


    public final function markComplete(int $id): JsonResponse
    {
        return $this->performActionOn($id, self::ACTION_COMPLETE);
    }

    public final function markIncomplete(int $id): JsonResponse
    {
        return $this->performActionOn($id, self::ACTION_INCOMPLETE);
    }

    private function validationRules(): array
    {
        return ['note' => 'required'];
    }

    /**
     * @param  int  $id
     * @return Builder|Model
     */
    private function getNote(int $id)
    {
        return Todo::query()->where('id', '=', $id)
            ->where('user_id', '=', \auth()->user()->getAuthIdentifier())->firstOrFail();
    }

    /**
     * @param  int  $id
     * @param  int  $action
     * @return JsonResponse
     */
    private function performActionOn(int $id, int $action = self::ACTION_COMPLETE): JsonResponse
    {
        try {
            $note = $this->getNote($id);

            if ($action === self::ACTION_COMPLETE) {
                $note->complete_at = time();
            } else {
                $note->complete_at = null;
            }

            $note->save();

            return response()->json($note->refresh(), 201);
        } catch (ModelNotFoundException $exception) {
            if (env('APP_ENV') === 'production') {
                return response()->json(['message' => 'note not found'], 202);
            } else {
                return response()->json(['message' => $exception->getMessage()], 202);
            }
        }
    }
}
