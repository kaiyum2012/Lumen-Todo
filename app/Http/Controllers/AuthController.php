<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiTokenGenerator;
use App\Traits\EmailRules;
use App\Traits\PasswordHelper;
use App\Traits\PasswordValidationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use EmailRules, PasswordHelper, PasswordValidationRules, ApiTokenGenerator;

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public final function register(Request $request): JsonResponse
    {
        $this->validate($request, $this->getValidationsRules(['email' => 'unique:users']));

        $user = User::query()->create([
            'email' => \request('email'),
            'password' => $this->generatePassword(\request('password')),
            'token' => $this->generateApiToken()
        ]);
        if ($user) {
            return response()->json($user);
        } else {
            return response()->json(['message' => 'invalid credentials'], '401');
        }
    }


    /** Single device/session login
     * upon login previous token will be replaced hence the session would get destroyed.
     * @param  Request  $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public final function login(Request $request): JsonResponse
    {
        $this->validate($request, $this->getValidationsRules());

        $user = User::query()->where('email', '=', request('email'))->first();

//        Note:: Resetting token to invalid all previous sessions.
//        for multi device or session token needs to move to separate table for multiple entries with TTL
        $user->token = $this->generateApiToken();
        $user->save();
        if (!empty($user) && Hash::check(request('password'), $user->password)) {
            return response()->json($user);
        } else {
            return response()->json(['message' => 'invalid credentials'], '401');
        }
    }

    /**
     * @return JsonResponse
     */
    public final function user(): JsonResponse
    {
        return response()->json(Auth::user());
    }

    /**
     * @param  array  $extra
     * @return array
     */
    private function getValidationsRules(array $extra = []): array
    {
        $rules = [
            'email' => $this->emailRules(),
            'password' => $this->passwordRules()
        ];
        foreach ($extra as $key => $value) {
            if (array_key_exists($key, $rules)) {
                $value = is_string($value) ? explode('|', $value) : $value;
                $rules[$key] = array_merge($rules[$key], $value);
            }
        }

        return $rules;
    }
}
