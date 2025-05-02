<?php

namespace App\Http\Resources\Bid;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BidCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => BidResource::collection($this->collection),
        ];
    }
}
