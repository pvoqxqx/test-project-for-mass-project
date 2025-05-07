<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API для управления пользователями"
 * )
 */
class UserController extends Controller
{
    /**
     * Показать информацию о пользователе.
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Получить информацию о пользователе",
     *     description="Получить информацию о пользователе и его заявках",
     *     security={{"bearerAuth":{}}},
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID пользователя"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о пользователе",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден"
     *     )
     * )
     * @throws AuthorizationException
     */
    public function show($id): UserResource
    {
        $this->authorize('show', auth()->user());

        $user = User::with('bids')->findOrFail($id);
        return new UserResource($user);
    }

    /**
     * @OA\Get(
     *     path="/api/email/verify/{id}/{hash}",
     *     tags={"Users"},
     *     summary="Подтверждение email",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="hash", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Email подтвержден"),
     *     @OA\Response(response=403, description="Неверный токен")
     * )
     */
    public function verify($id, $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string)$hash, sha1($user->getEmailForVerification()))) {
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
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     summary="Удаление пользователя (только для администратора)",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Пользователь удален"),
     *     @OA\Response(response=403, description="Доступ запрещен")
     * )
     * @throws AuthorizationException
     */
    public function destroy($id): JsonResponse
    {
        $this->authorize('delete', auth()->user());

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Пользователь удален.']);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     summary="Обновление профиля (только для обычного пользователя и админа)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(response=200, description="Данные успешно обновлены"),
     *     @OA\Response(response=403, description="Редактирование разрешено только для пользователей и администраторов"),
     *     @OA\Response(response=404, description="Пользователь не найден")
     * )
     * @throws AuthorizationException
     */
    public function update(UserUpdateRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $this->authorize('update', $user);

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Данные успешно обновлены.',
            'user' => new UserResource($user),
        ]);
    }
}
