<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentReminder extends Notification
{
    use Queueable;

    protected $schedule;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function sendSMS($contact)
    {
        $message = "Payment Reminder: You have an upcoming payment of PHP " .
            number_format($this->schedule->amount_due, 2) .
            " due on " . $this->schedule->due_date .
            ". Please ensure timely payment to avoid penalties.";

        return Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => 'd6c11eecdd39bbf6780e0bcd8f26722c',
            'number' => $contact,
            'message' => $message,
            'sendername' => 'Rhean Motor Center Inc.',
        ]);
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
