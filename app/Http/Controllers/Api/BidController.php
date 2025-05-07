<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bid\BidStoreRequest;
use App\Http\Requests\Bid\BidUpdateRequest;
use App\Http\Requests\Bid\FilterBidRequest;
use App\Http\Resources\Bid\BidCollection;
use App\Http\Resources\Bid\BidResource;
use App\Jobs\ProcessBid;
use App\Models\Bid;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Bids",
 *     description="API для управления заявками"
 * )
 */
class BidController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/bids",
     *     tags={"Bids"},
     *     summary="Получить список заявок",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Успешный ответ", @OA\JsonContent(ref="#/components/schemas/BidCollection")),
     *     @OA\Response(response=401, description="Неавторизованный доступ"),
     *     @OA\Response(response=403, description="Доступ запрещён")
     * )
     * @throws AuthorizationException
     */
    public function index(FilterBidRequest $request): BidCollection
    {
        $this->authorize('index', Bid::class);

        $query = Bid::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortField = in_array($request->sort_by, ['created_at', 'email', 'name', 'status'])
            ? $request->sort_by
            : 'created_at';

        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';

        return new BidCollection(
            $query->orderBy($sortField, $sortDir)->paginate(10)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/bids/{id}",
     *     tags={"Bids"},
     *     summary="Получить заявку по ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Успешный ответ"),
     *     @OA\Response(response=404, description="Заявка не найдена")
     * )
     * @throws AuthorizationException
     */
    public function show(int $id): BidResource
    {
        $bid = Bid::with('user')->findOrFail($id);

        $this->authorize('show', $bid);

        return new BidResource($bid);
    }

    /**
     * @OA\Post(
     *     path="/api/bids",
     *     summary="Создание новой заявки",
     *     description="Создание заявки доступно только авторизированным пользователям",
     *     tags={"Bids"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "message"},
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
     *             @OA\Property(property="message", type="string", example="Мне нужна консультация")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заявка успешно создана"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещён"
     *     )
     * )
     * @throws AuthorizationException
     */
    public function store(BidStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Bid::class);

        $validated = $request->validated();
        $userId = auth()->id();

        ProcessBid::dispatch($validated, $userId);

        return response()->json(['message' => 'Заявка будет обработана в ближайшее время'], 202);
    }

    /**
     * @OA\Put(
     *     path="/api/bids/{id}",
     *     tags={"Bids"},
     *     summary="Обновление заявки",
     *     description="Обновление заявки доступно только админам и модераторам",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Resolved, Reject"),
     *             @OA\Property(property="comment", type="string", format="email", example="Комментарий"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Заявка успешно обновлена"),
     *     @OA\Response(response=403, description="Доступ запрещён")
     * )
     * @throws AuthorizationException
     */
    public function update(BidUpdateRequest $request, int $id): BidResource
    {
        $this->authorize('update', Bid::class);

        $bid = Bid::findOrFail($id);
        $bid->update($request->validated());

        return new BidResource($bid);
    }

    /**
     * @OA\Delete(
     *     path="/api/bids/{id}",
     *     tags={"Bids"},
     *     summary="Удаление заявки",
     *     description="Удаление заявки доступно только админам",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Заявка успешно удалена"),
     *     @OA\Response(response=403, description="Доступ запрещён")
     * )
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Bid::class);

        $bid = Bid::findOrFail($id);
        $bid->delete();

        return response()->json(['message' => 'Заявка успешно удалена']);
    }
}
