<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $loans = Loan::whereDate('due_date', '<=', now()->addDays(7))->get();

        // foreach ($loans as $loan) {
        //     // Example pseudo-logic
        //     Notification::send($loan->user, new PaymentReminder($loan));
        // }

        $this->info('Payment reminders sent.');
    }
}
