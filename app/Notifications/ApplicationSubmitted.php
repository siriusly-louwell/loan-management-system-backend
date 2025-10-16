<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmitted extends Notification
{
    use Queueable;

    protected $applicantName;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($applicantName)
    {
        $this->applicantName = $applicantName;
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
        return (new MailMessage)
            ->subject('Application Received')
            ->greeting('Hello ' . $this->applicantName . '!')
            ->line('Your loan application has been submitted successfully.')
            ->line('The application is now under review by the staff.')
            ->line('You can track your application in our website, by inputting your record ID in the `Find My Application` tab.')
            ->salutation('Best regards, Rhean Motor Center Inc.');
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
