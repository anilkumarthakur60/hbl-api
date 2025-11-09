<?php

namespace App\Console\Commands;

use Anil\Hbl\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->transactionStatus();
    }

    public function transactionStatus()
    {
        $payment = new Payment;
        $response = $payment->executeFormJose(
            amount: 1,
            orderNo: Str::random(15),
        );

        $response = json_decode($response);
        dd($response->response->data->paymentPage->paymentPageURL);
    }
}
