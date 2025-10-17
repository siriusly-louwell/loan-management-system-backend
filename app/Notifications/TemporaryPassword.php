<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemporaryPassword extends Notification
{
    use Queueable;

    protected $userName;
    protected $role;
    protected $tempPassword;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($userName, $role, $tempPassword)
    {
        $this->userName = $userName;
        $this->role = $role;
        $this->tempPassword = $tempPassword;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return $this->role == "customer"
            ? (new MailMessage)
                ->subject('Welcome to Rhean Motor Center — Your Customer Account')
                ->greeting('Hello ' . $this->userName . ',')
                ->line('We’re delighted to inform you that your loan application has been approved and your customer account has been created.')
                ->line('You can now log in to your account to track your transactions, view payment details, and manage your motorcycle financing.')
                ->line('**Email:** ' . e($notifiable->email))
                ->action('Login to My Account', url('http://localhost:3000/login'))
                ->line('If you did not request or expect this account, please contact us immediately.')
                ->salutation('Thank you for choosing Rhean Motor Center Inc.')
            : (new MailMessage)
                ->subject('Welcome to Rhean Motor Loan Management System')
                ->greeting('Hello ' . $this->userName . ',')
                ->line('You have been added as a **' . ucfirst($this->role) . '** in the Rhean Motor Loan Management System.')
                ->line('To get started, please log in using the credentials below:')
                ->line('**Email:** ' . e($notifiable->email))
                ->line('**Temporary Password:** `' . e($this->tempPassword) . '`')
                ->line('You’ll be prompted to change your password after your first login for security purposes.')
                ->action('Login to System', url('http://localhost:3000/login'))
                ->line('If you were not expecting this invitation, please contact your system administrator immediately.')
                ->salutation('Welcome aboard, **Rhean Motor Center Inc.**');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
