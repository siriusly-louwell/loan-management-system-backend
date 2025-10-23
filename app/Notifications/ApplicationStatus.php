<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ApplicationStatus extends Notification
{
    use Queueable;

    protected $statusData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($statusData)
    {
        $this->statusData = $statusData;
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
        switch ($this->statusData['status']) {
            case 'accepted':
                return $this->acceptedMail();
            case 'denied':
                return $this->deniedMail();
            case 'approved':
                $this->approvedSMS($notifiable->contact);
                return $this->approvedMail();
            case 'declined':
                $this->declinedSMS($notifiable->contact);
                return $this->declinedMail();
            default:
                return $this->genericMail();
        }
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

    protected function acceptedMail()
    {
        return (new MailMessage)
            ->subject('Application Accepted — Pending Evaluation')
            ->line('Your application has been **accepted** by our staff after verification of your submitted details.')
            ->line('It is now being forwarded to our Credit Investigation (CI) team for further evaluation.')
            ->line('You may continue tracking the progress of your application using the record ID below:')
            ->line('**Record ID:** `' . $this->statusData['recordID'] . '`')
            ->action('Track My Application', url('http://localhost:3000/find'))
            ->salutation('Thank you for your continued patience, **Rhean Motor Center Inc.**');
    }

    protected function deniedMail()
    {
        $reasonType = '';

        switch ($this->statusData['type']) {
            case 'incorrect_info':
                $reasonType =  'Incorrect inputted values';
                break;
            case 'incorrect_file':
                $reasonType  = 'Wrong requirements uploaded';
                break;
            default:
                $reasonType = 'Unmet standards';
        }

        $message = (new MailMessage)
            ->subject('Application Denied')
            ->line('After reviewing your application, we found issues that prevent us from proceeding at this time.')
            ->line('Below are the details provided by our staff:')
            ->line('**' . $reasonType . '**')
            ->line($this->statusData['message']);

        if (isset($this->statusData['resubmit']) && $this->statusData['resubmit'] === 'yes') {
            $message->line('You may correct the issues and reapply through our online portal.')
                ->action('Reapply Now', url('#'));
        }

        return $message->salutation('Thank you for your interest, **Rhean Motor Center Inc.**');
    }

    protected function approvedMail()
    {
        return (new MailMessage)
            ->subject('Loan Application Approved')
            ->line('Congratulations! Your loan application has been **approved** after full evaluation by our Credit Investigation team and administrative review.')
            ->line('Your loan is now valid and ready for processing. Our staff will contact you shortly to finalize your documentation and release schedule.')
            ->line('**Record ID:** `' . $this->statusData['recordID'] . '`')
            ->action('View Application Details', url('http://localhost:3000/find'))
            ->salutation('We appreciate your trust in us, **Rhean Motor Center Inc.**');
    }

    protected function declinedMail()
    {
        return (new MailMessage)
            ->subject('Application Declined')
            ->line('After final evaluation by our administrative team, your loan application has been **declined**.')
            ->line('We encourage you to review our application requirements and submit a new request if your circumstances change.')
            ->line($this->statusData['message'])
            ->action('View Application Portal', url('/apply'))
            ->salutation('Sincerely, **Rhean Motor Center Inc.**');
    }

    protected function genericMail()
    {
        return (new MailMessage)
            ->subject('Application Status Updated')
            ->line('The status of your application has been updated.')
            ->line('**Record ID:** `' . $this->statusData['recordID'] . '`')
            ->action('View Status', url('http://localhost:3000/find'))
            ->salutation('Best regards, **Rhean Motor Center Inc.**');
    }

    public function approvedSMS($contact)
    {
        $message =
            "Congratulations! Your loan application has been approved after full evaluation by our Credit Investigation team and administrative review.\n" .
            "Your loan is now valid and ready for processing. Our staff will contact you shortly to finalize your documentation and release schedule.\n" .
            "Record ID: `" . $this->statusData['recordID'] . "`\n" .
            "View Application Details: http://localhost:3000/find";

        Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => 'd6c11eecdd39bbf6780e0bcd8f26722c',
            'number' => $contact,
            'message' => $message,
            'sendername' => 'Rhean Motor Center Inc.',
        ]);
    }

    public function declinedSMS($contact)
    {
        $message =
            "After final evaluation by our administrative team, your loan application has been declined.\n" .
            "We encourage you to review our application requirements and submit a new request if your circumstances change.\n" .
            "View Application Details: http://localhost:3000/find";

        Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => 'd6c11eecdd39bbf6780e0bcd8f26722c',
            'number' => $contact,
            'message' => $message,
            'sendername' => 'Rhean Motor Center Inc.',
        ]);
    }
}
