<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Регистрация нового пользователя.
     */
    public function register(UserStoreRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role_id'  => 3, // обычный пользователь
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Пользователь зарегистрирован. Проверьте email для подтверждения.',
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * Подтверждение email.
     */
    public function verify($id, $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Неверный токен.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email уже подтвержден.'], 200);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email успешно подтвержден.'], 200);
    }

    /**
     * Удаление пользователя (только для администратора).
     */
    public function destroy($id): JsonResponse
    {
        $admin = Auth::user();

        if ($admin->role_id !== 1) {
            return response()->json(['message' => 'Доступ запрещен.'], 403);
        }

        $target = User::findOrFail($id);
        $target->delete();

        return response()->json(['message' => 'Пользователь удален.']);
    }

    /**
     * Обновление профиля (только для обычного пользователя).
     */
    public function update(UserUpdateRequest $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->role_id !== 3 || $user->role_id !== 1) {
            return response()->json(['message' => 'Редактирование разрешено только для пользователей и администраторов.'], 403);
        }

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Данные успешно обновлены.',
            'user'    => new UserResource($user),
        ]);
    }
}
