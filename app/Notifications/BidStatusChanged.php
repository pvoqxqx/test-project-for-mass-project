<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BidStatusChanged extends Notification
{
    public string $oldStatus;
    public string $newStatus;
    private string $comment;

    public function __construct(string $oldStatus, string $newStatus, string $comment)
    {
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->comment = $comment;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Изменение статуса заявки')
            ->line("Статус вашей заявки изменен: {$this->oldStatus} → {$this->newStatus}.
            Сообщение от модерации: {$this->comment}");
    }
}
