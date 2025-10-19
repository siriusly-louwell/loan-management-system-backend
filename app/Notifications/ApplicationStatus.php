<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatus extends Notification
{
    use Queueable;

    protected $status;
    protected $recordID;
    protected $reasons;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($status, $recordID = null, $reasons = null)
    {
        $this->status = $status;
        $this->recordID = $recordID;
        $this->reasons = $reasons;
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
        switch ($this->status) {
            case 'accepted':
                return $this->acceptedMail();
            case 'denied':
                return $this->deniedMail();
            case 'approved':
                return $this->approvedMail();
            case 'declined':
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
            ->line('**Record ID:** `' . $this->recordID . '`')
            ->action('Track My Application', url('/find-application?record=' . $this->recordID))
            ->salutation('Thank you for your continued patience,<br>**Rhean Motor Center Inc.**');
    }

    protected function deniedMail()
    {
        return (new MailMessage)
            ->subject('Application Denied')
            ->line('After reviewing your application, we found issues that prevent us from proceeding at this time.')
            ->line('Below are the details provided by our staff:')
            ->line('<<Reasons here>>')
            ->line('You may correct the issues and reapply through our online portal.')
            ->action('Reapply Now', url('/apply'))
            ->salutation('Thank you for your interest,<br>**Rhean Motor Center Inc.**');
    }

    protected function approvedMail()
    {
        return (new MailMessage)
            ->subject('Loan Application Approved')
            ->line('Congratulations! Your loan application has been **approved** after full evaluation by our Credit Investigation team and administrative review.')
            ->line('Your loan is now valid and ready for processing. Our staff will contact you shortly to finalize your documentation and release schedule.')
            ->line('**Record ID:** `' . $this->recordID . '`')
            ->action('View Application Details', url('/find-application?record=' . $this->recordID))
            ->salutation('We appreciate your trust in us,<br>**Rhean Motor Center Inc.**');
    }

    protected function declinedMail()
    {
        return (new MailMessage)
            ->subject('Application Declined')
            ->line('After final evaluation by our administrative team, your loan application has been **declined**.')
            ->line('We encourage you to review our application requirements and submit a new request if your circumstances change.')
            ->line('<<Reasons here>>')
            ->action('View Application Portal', url('/apply'))
            ->salutation('Sincerely,<br>**Rhean Motor Center Inc.**');
    }

    protected function genericMail()
    {
        return (new MailMessage)
            ->subject('Application Status Updated')
            ->line('The status of your application has been updated.')
            ->line('**Record ID:** `' . $this->recordID . '`')
            ->action('View Status', url('/find-application?record=' . $this->recordID))
            ->salutation('Best regards,<br>**Rhean Motor Center Inc.**');
    }
}
