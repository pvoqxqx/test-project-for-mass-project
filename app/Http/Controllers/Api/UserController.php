<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API для управления пользователями"
 * )
 */
class UserController extends Controller
{
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
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Удаление пользователя (только для администратора)",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Пользователь удален"),
     *     @OA\Response(response=403, description="Доступ запрещен")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $this->authorizeRoles([1]);

        $target = User::findOrFail($id);
        $target->delete();

        return response()->json(['message' => 'Пользователь удален.']);
    }

    /**
     * @OA\Put(
     *     path="/api/user/{id}",
     *     tags={"Users"},
     *     summary="Обновление профиля (только для обычного пользователя)",
     *     security={{"bearerAuth":{}}},
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
     */
    public function update(UserUpdateRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $this->authorizeRoles([1,3], 'Редактирование разрешено только для пользователей и администраторов.');

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

    private function authorizeRoles(array $roles, string $message = ''): void
    {
        if (!in_array(Auth::user()->role_id, $roles)) {
            abort(403, $message ?: 'Access denied');
        }
    }

}
