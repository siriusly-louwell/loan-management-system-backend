<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Notifications\PaymentReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders to customers with upcoming due dates';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $oneWeekFromNow = Carbon::now()->addWeek();

        $schedules = Schedule::with(['application_form.user'])
            ->whereDate('due_date', '=', $oneWeekFromNow->toDateString())
            ->where('status', '=', 'pending')
            ->get();

        $count = 0;
        foreach ($schedules as $schedule) {
            if ($schedule->application_form && $schedule->application_form->user) {
                $reminder = new PaymentReminder($schedule);
                $reminder->sendSMS($schedule->application_form->user->contact);
                $count++;
            }
        }

        $this->info("Sent {$count} payment reminders.");
        return Command::SUCCESS;
    }
}
