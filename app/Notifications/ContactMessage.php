<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessage extends Notification
{
    use Queueable;

    public function __construct(
        public string $name,
        public string $email,
        public string $messageText,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Contact Message')
            ->line('Name: '.$this->name)
            ->line('Email: '.$this->email)
            ->line('Message:')
            ->line($this->messageText);
    }
}

