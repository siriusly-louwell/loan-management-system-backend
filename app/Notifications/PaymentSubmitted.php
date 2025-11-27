<?php

namespace App\Notifications;

use App\Models\Motorcycle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicantName;
    protected $recordID;
    protected $contact;
    protected $amount;
    private $motorcycle;
    private $balance;
    private $transaction;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($applicantName, $contact_num, $amount, $balance, $transaction)
    {
        $this->applicantName = $applicantName;
        $this->contact = $contact_num;
        $this->amount = $amount;
        $this->balance = $balance;
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
        $this->toSMS($this->contact);
        return (new MailMessage)
            ->subject('Your Loan Application Payment Has Been Received')
            ->greeting('Hello ' . $this->applicantName . ',')
            ->line('Thank you for your payment towards your loan application with **Rhean Motor Center Inc.**')
            ->line('We have successfully received your payment, and your loan application is being updated.')
            ->line('Here are the details of your transaction:')
            ->line('**Motorcycle:** `' . $this->motorcycle['name'] . '`')
            ->line('**Brand:** `' . $this->motorcycle['brand'] . '`')
            ->line('**Amount Paid:** ₱`' . $this->amount . '`')
            ->line('**Current Balance:** ₱`' . $this->balance . '`')
            ->line('You can track the status of your application anytime by visiting our website and entering your record ID below.')
            ->line('**Record ID:** `' . $this->recordID . '`')
            ->action('Find My Application', url('https://rhean-loan-management-system.xyz/find'))
            ->line('If you have any questions, feel free to reach out to us. We’ll be glad to assist you.')
            ->salutation('Warm regards, **Rhean Motor Center Inc.**');
    }


    /**
     * Send SMS
     */
    public function toSMS($contact)
    {
        $message = 
            "Rhean Motor Center: Your payment of ₱{$this->amount} towards your loan application (Record ID: {$this->recordID}) has been received.\n" .
            "Motorcycle: {$this->motorcycle['brand']} {$this->motorcycle['name']}\n" .
            "Amount Paid: ₱{$this->amount}\n" .
            "Current Balance: ₱{$this->balance}\n" .
            "Track status: https://rhean-loan-management-system.xyz/find";

        Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => 'd6c11eecdd39bbf6780e0bcd8f26722c',
            'number' => $contact,
            'message' => $message,
            'sendername' => 'RheanMotor',
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
