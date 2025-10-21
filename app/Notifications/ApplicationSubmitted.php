<?php

namespace App\Notifications;

use App\Models\Motorcycle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApplicationSubmitted extends Notification
{
    use Queueable;

    protected $applicantName;
    protected $recordID;
    protected $transaction;
    private $motorcycle;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($applicantName, $recordId, $transaction)
    {
        $this->applicantName = $applicantName;
        $this->recordID = $recordId;
        $this->transaction = $transaction;
        $this->motorcycle = Motorcycle::where('id', $transaction['motorcycle_id'])->firstOrFail();
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
        $transaction = $this->transaction;
        // $motorcycle = Motorcycle::where('id', $transaction['motorcycle_id'])->firstOrFail();

        return (new MailMessage)
            ->subject('Your Loan Application Has Been Received')
            ->greeting('Hello ' . $this->applicantName . ',')
            ->line('Thank you for submitting your loan application to **Rhean Motor Center Inc.**')
            ->line('We have successfully received your application and it is now under review by our staff.')
            ->line('Here are the details of your transaction:')
            ->line('**Motorcycle:** `' . $this->motorcycle['name'] . '`')
            ->line('**Brand:** `' . $this->motorcycle['brand'] . '`')
            ->line('**Color:** `' . $transaction['color'] . '`')
            ->line('**Down Payment:** ₱`' . $transaction['downpayment'] . '`')
            ->line('**Quantity:** `' . $transaction['quantity'] . '` unit/s')
            ->line('**Tenure:** `' . $transaction['tenure'] . '` year/s')
            ->line('You can track the status of your application anytime by visiting our website and entering your record ID below.')
            ->line('**Record ID:** `' . $this->recordID . '`')
            ->action('Find My Application', url('http://localhost:3000/find'))
            ->line('If you have any questions, feel free to reach out to us. We’ll be glad to assist you.')
            ->salutation('Warm regards, **Rhean Motor Center Inc.**');
    }

    /**
     * Send SMS
     */
    public function toSms($notifiable, $motorcycle, $transaction)
    {
        $message =
            "Rhean Motor Center: Your loan application (Record ID: {$this->recordID}) has been received.\n" .
            "Motorcycle: {$motorcycle['brand']} {$motorcycle['name']}\n" .
            "Downpayment: ₱{$transaction['downpayment']}\n" .
            "Color: {$transaction['color']}\n" .
            "Tenure: {$transaction['tenure']} year/s\n" .
            "Track status: rhean-motor-center.com/find";

        Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => env('SEMAPHORE_API_KEY'),
            'number' => $notifiable->phone_number,
            'message' => $message,
            'sendername' => 'RHEANMC',
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
