<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Bid Management API",
 *     version="1.0",
 *     description="API для подачи и обработки заявок пользователями и администрацией."
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Основной сервер API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Components(
 *     @OA\Schema(
 *         schema="UserCollection",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/UserResource")
 *     ),
 *     @OA\Schema(
 *         schema="UserResource",
 *         type="object",
 *         required={"id", "name", "email", "created_at", "updated_at"},
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Иван"),
 *         @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-02T10:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-02T10:00:00Z")
 *     ),
 *     @OA\Schema(
 *         schema="UserUpdateRequest",
 *         type="object",
 *         required={"name", "email"},
 *         @OA\Property(property="name", type="string", example="Иван"),
 *         @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *         @OA\Property(property="password", type="string", nullable=true, example="newpassword123"),
 *         @OA\Property(property="password_confirmation", type="string", nullable=true, example="newpassword123")
 *     ),
 *     @OA\Schema(
 *         schema="BidCollection",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/BidResource")
 *     ),
 *     @OA\Schema(
 *         schema="Bid",
 *         type="object",
 *         required={"name", "email", "message"},
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string", example="Иван"),
 *         @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *         @OA\Property(property="message", type="string", example="Мне нужна консультация"),
 *         @OA\Property(property="status", type="string", example="Active"),
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *     ),
 *     @OA\Schema(
 *         schema="BidResource",
 *         type="object",
 *         required={"name", "email", "message", "status", "created_at", "updated_at"},
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string", example="Иван"),
 *         @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *         @OA\Property(property="message", type="string", example="Мне нужна консультация"),
 *         @OA\Property(property="status", type="string", example="Active"),
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *    ),
 *    @OA\Schema(
 *        schema="UserRegisterResource",
 *        type="object",
 *        required={"id", "name", "email", "created_at", "updated_at"},
 *        @OA\Property(property="name", type="string", example="Иван"),
 *        @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *    ),
 * )
 */
class OASpec {}
