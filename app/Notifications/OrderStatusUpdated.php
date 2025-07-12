<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\Order $order
     * @param array $changes
     */
    public function __construct(protected $order, protected $changes = [])
    {}
    

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Order #{$this->order->id} Update")
            ->greeting("Hello {$notifiable->name}!")
            ->line("We're writing to inform you about an update to your order #{$this->order->id}.");

        // If status was updated
        if (isset($this->changes['status'])) {
            $statusLabel = ucfirst(strtolower($this->changes['status']));
            $message->line("Your order status has been updated to: **{$statusLabel}**");
            
            // Add specific messaging based on status
            if ($this->changes['status'] === 'shipped') {
                $message->line("Your order is on its way to you!");
            } elseif ($this->changes['status'] === 'delivered') {
                $message->line("Your order has been delivered. We hope you enjoy your purchase!");
            }
        }

        // If tracking code was added or updated
        if (isset($this->changes['tracking_code']) && !empty($this->changes['tracking_code'])) {
            $trackingCode = $this->changes['tracking_code'];
            $message->line("A tracking code has been assigned to your order: **{$trackingCode}**");
            $message->line("You can use this code to track your package with the shipping carrier.");
        }

        // Add view order button
        $orderUrl = url("/orders/{$this->order->id}");
        $message->action('View Order Details', $orderUrl);
        
        $message->line('Thank you for shopping with ' . config('app.name') . '!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'changes' => $this->changes,
        ];
    }
}
