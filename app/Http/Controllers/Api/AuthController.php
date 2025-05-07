<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="API для авторизации и аутентификации пользователей"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Регистрация нового пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь зарегистрирован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь зарегистрирован. Проверьте email для подтверждения."),
     *             @OA\Property(property="user", ref="#/components/schemas/UserRegisterResource")
     *         )
     *     )
     * )
     */
    public function register(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('YourAppName')->accessToken;

        return response()->json([
            'message' => 'Пользователь зарегистрирован. Проверьте email для подтверждения.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Авторизация пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="denis.klevtsov@bk.ru"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная авторизация",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Неверный логин или пароль")
     * )
     * @throws ValidationException
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $user = Auth::user();

            $token = $user->createToken('YourAppName')->accessToken;

            return response()->json([
                'message' => 'Авторизация успешна',
                'token'   => $token,
                'user'    => new UserResource($user),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Неверный логин или пароль'],
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Выход пользователя",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Выход успешен"),
     *     @OA\Response(response=401, description="Неавторизованный доступ")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens->each(function ($token) {
                $token->delete();
            });

            return response()->json(['message' => 'Выход успешен']);
        }

        return response()->json(['message' => 'Неавторизованный доступ'], 401);
    }
}


