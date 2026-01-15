<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceAlert extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $symbol,
        public int $quantity,
        public float $buyPrice,
        public float $currentPrice,
        public float $thresholdPrice
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->type === 'SELL' ? 'Sell Alert' : 'Buy More Alert';

        return (new MailMessage)
            ->subject($subject.' '.$this->symbol)
            ->line('Symbol: '.$this->symbol)
            ->line('Quantity: '.$this->quantity)
            ->line('Buy Price: '.$this->buyPrice)
            ->line('Current Price: '.$this->currentPrice)
            ->line('Threshold Price: '.$this->thresholdPrice);
    }
}

