<?php

namespace App\Mail;


use App\Models\Fulfillment;

use App\Models\Order;
use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $fulfillment;
    public $order;
    public function __construct()
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
//        $user_shop = Session::find($this->fulfillment->session_id);
        $data = array(

        );
        return $this->from('haseebbutt0311@gmail.com',"Haseeb123")->subject('Test Email')->view('emails.test_mail')->with($data);
    }
}
