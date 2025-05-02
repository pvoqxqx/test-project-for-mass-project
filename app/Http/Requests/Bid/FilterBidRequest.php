<?php

namespace App\Http\Requests\Bid;

use Illuminate\Foundation\Http\FormRequest;

class FilterBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:Active,Resolved, Reject',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'sort_by' => 'sometimes|in:created_at,email,name,status',
            'sort_dir' => 'sometimes|in:asc,desc',
        ];
    }
}
