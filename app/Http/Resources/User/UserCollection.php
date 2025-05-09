<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => UserResource::collection($this->collection),
        ];
    }
}
