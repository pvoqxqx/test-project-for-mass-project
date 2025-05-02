<?php

namespace App\Jobs;

use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    public int $userId;

    public function __construct(array $data, int $userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        Bid::create([
            ...$this->data,
            'user_id' => $this->userId,
        ]);

        // добавить уведомления, логирование и т.п.
    }
}

