<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OssReportGenerated extends Notification
{
    use Queueable;

    public function __construct(protected string $month)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('OSS Report Generated')
            ->line("Your OSS report for {$this->month} has been generated.")
            ->line('Thank you for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'month' => $this->month,
        ];
    }
}
