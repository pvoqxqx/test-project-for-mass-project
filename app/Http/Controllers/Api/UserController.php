<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Регистрация нового пользователя.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        return response()->json(['message' => 'Пользователь зарегистрирован. Проверьте email для подтверждения.'], 201);
    }

    /**
     * Подтверждение email.
     */
    public function verify(Request $request, $id, $hash)
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
    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role_id !== 1) {
            return response()->json(['message' => 'Доступ запрещен.'], 403);
        }

        $target = User::findOrFail($id);
        $target->delete();

        return response()->json(['message' => 'Пользователь удален.']);
    }

    /**
     * Обновление профиля (только для обычного пользователя).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id !== 3) {
            return response()->json(['message' => 'Редактирование разрешено только для пользователей.'], 403);
        }

        $data = $request->only(['name', 'password']);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['message' => 'Данные успешно обновлены.']);
    }
}
