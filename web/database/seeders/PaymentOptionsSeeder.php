<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_options')->delete();
        $payment_options = array(
            array('payment_option' => "Credit Card"),
            array('payment_option' => "Deferred"),
            array('payment_option' => "Local"),
            array('payment_option' => "Manual Payment"),
            array('payment_option' => "Offsite"),
            array('payment_option' => "Payment on Delivery"),
            array('payment_option' => "Redeemable (gift card, store credit..."),
            array('payment_option' => "Wallet (PayPal, Apple Pay, etc)"),
            array('payment_option' => "Other"),
        );
        DB::table('payment_options')->insert($payment_options);
    }
}
