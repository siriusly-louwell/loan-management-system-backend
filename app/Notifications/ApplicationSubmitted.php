<?php

namespace App\Notifications;

use App\Models\Motorcycle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ApplicationSubmitted extends Notification
{
    use Queueable;

    protected $applicantName;
    protected $recordID;
    protected $transaction;

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
        $motorcycle = Motorcycle::where('id', $transaction['motorcycle_id'])->firstOrFail();

        return (new MailMessage)
            ->subject('Your Loan Application Has Been Received')
            ->greeting('Hello ' . $this->applicantName . ',')
            ->line('Thank you for submitting your loan application to **Rhean Motor Center Inc.**')
            ->line('We have successfully received your application and it is now under review by our staff.')
            ->line('Here are the details of your transaction:')
            ->line('**Motorcycle:** `' . $motorcycle['name'] . '`')
            ->line('**Brand:** `' . $motorcycle['brand'] . '`')
            ->line('**Color:** `' . $transaction['color'] . '`')
            ->line('**Down Payment:** ₱`' . $transaction['downpayment'] . '`')
            ->line('**Quantity:** `' . $transaction['quantity'] . '` unit/s')
            ->line('**Tenure:** `' . $transaction['tenure'] . '` year/s')
            // ->line($this->buildTransactionTable())
            ->line('You can track the status of your application anytime by visiting our website and entering your record ID below.')
            ->line('**Record ID:** `' . $this->recordID . '`')
            ->action('Find My Application', url('http://localhost:3000/find'))
            ->line('If you have any questions, feel free to reach out to us. We’ll be glad to assist you.')
            ->salutation('Warm regards, **Rhean Motor Center Inc.**');
    }

    /**
     * Builds a simple HTML table for transaction data.
     */
    protected function buildTransactionTable()
    {
        $transaction = $this->transaction;

        $colorPreview = '<span style="display:inline-block; width:14px; height:14px; background-color: '
            . e($transaction['color']) . '; border: 1px solid #ccc; vertical-align:middle; margin-right:6px;"></span>'
            . e($transaction['color']);

        $table = <<<HTML
        <table style="width:100%; border-collapse: collapse; margin-top:12px;">
            <tr>
                <td style="padding:6px 0; color:#111827;"><strong>Motorcycle:</strong></td>
                <td style="padding:6px 0;">{$transaction['motorcycle_id']}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#111827;"><strong>Color:</strong></td>
                <td style="padding:6px 0;">{$colorPreview}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#111827;"><strong>Downpayment:</strong></td>
                <td style="padding:6px 0;">₱{$transaction['downpayment']}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#111827;"><strong>Quantity:</strong></td>
                <td style="padding:6px 0;">{$transaction['quantity']}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#111827;"><strong>Tenure:</strong></td>
                <td style="padding:6px 0;">{$transaction['tenure']} months</td>
            </tr>
        </table>
        HTML;

        return $table;
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
