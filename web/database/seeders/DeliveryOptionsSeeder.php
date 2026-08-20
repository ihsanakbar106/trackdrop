<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('delivery_options')->delete();
        $delivery_options = array(
            array('delivery_option' => "Shipping"),
            array('delivery_option' => "Local"),
            array('delivery_option' => "Pickup"),
            array('delivery_option' => "Pickup Point"),
        );
        DB::table('delivery_options')->insert($delivery_options);
    }
}
