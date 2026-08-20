<?php

namespace App\Mail;


use App\Models\Fulfillment;

use App\Models\Order;
use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckShippingStatus extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $fulfillment;
    public $order;
    public function __construct(Fulfillment $fulfillment,Order $order)
    {
        $this->fulfillment = $fulfillment;
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        $user_shop = Session::find($this->fulfillment->session_id);
        $data = array(
            "fulfillment" => $this->fulfillment,
            "order" => $this->order,
            "user_shop" => $user_shop,
        );
        return $this->subject('Your Order Shipping Status')->view('emails.CheckShippingStatus')->with($data);
    }
}
