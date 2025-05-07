<?php

namespace App\Jobs;

use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        if ($this->userId) {
            $bid = Bid::create([
                ...$this->data,
                'user_id' => $this->userId,
            ]);

//            Log::info("Заявка создана пользователем ID: {$this->userId}", [
//                'bid_id' => $bid->id,
//                'user_id' => $this->userId,
//                'data' => $this->data
//            ]);
        } else {
            Log::error("Пользователь с ID {$this->userId} не найден для создания заявки.");
        }
    }
}
