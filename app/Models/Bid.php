<?php

namespace App\Models;

use App\Notifications\BidStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bid extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'status',
        'message',
        'comment',
    ];

    protected static function booted(): void
    {
        static::updated(function ($bid) {
            if ($bid->isDirty('status')) {
                $originalStatus = $bid->getOriginal('status');
                $newStatus = $bid->status;
                $comment = $bid->comment;

                if ($bid->user && $bid->user->email) {
                    $bid->user->notify(new BidStatusChanged($originalStatus, $newStatus, $comment));
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
