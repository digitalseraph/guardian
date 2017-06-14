<?php

namespace WhiteSunrise\Guardian\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetAdminPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Notifications sent via email
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    //Content of email sent to the Seller
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->line('You are receiving this email because we received a password reset request for your account.')
        ->action('Reset Password', url('admin/password/reset', $this->token))
        ->line('If you did not request a password reset, no further action is required.');
    }
}
