<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bid\FilterBidRequest;
use App\Http\Requests\Bid\BidStoreRequest;
use App\Http\Requests\Bid\BidUpdateRequest;
use App\Http\Resources\Bid\BidCollection;
use App\Http\Resources\Bid\BidResource;
use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    public function index(FilterBidRequest $request): BidCollection
    {
        $this->authorizeRoles([1, 2]); // Админ или модератор

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

    public function show(int $id): BidResource
    {
        $bid = Bid::findOrFail($id);
        return new BidResource($bid);
    }

    public function store(BidStoreRequest $request): BidResource
    {
        $this->authorizeRoles([3]); // Только пользователь

        $bid = Bid::create([
            ...$request->validated(),
            'status' => 'Active',
        ]);

        return new BidResource($bid);
    }

    public function update(BidUpdateRequest $request, int $id): BidResource
    {
        $this->authorizeRoles([1, 2]); // Админ или модератор

        $bid = Bid::findOrFail($id);
        $bid->update($request->validated());

        return new BidResource($bid);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorizeRoles([1]); // Только админ

        $bid = Bid::findOrFail($id);
        $bid->delete();

        return response()->json(['message' => 'Заявка успешно удалена']);
    }

    private function authorizeRoles(array $roles): void
    {
        if (!in_array(Auth::user()->role_id, $roles)) {
            abort(403, 'Access denied');
        }
    }
}
