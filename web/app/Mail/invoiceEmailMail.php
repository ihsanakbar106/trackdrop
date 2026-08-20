<?php

namespace App\Mail;

use App\Models\Session;
use http\Client\Curl\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class invoiceEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $order;

    public function __construct($order)
    {
        $this->order = $order;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = $this->order;
        $session = Session::find($order->session_id);

        $data2 = array(
            'order'=>$order,
            'session'=>$session,
        );
        return $this->subject("Your Feedback Matters – $order->name")->view('emails.invoiceEmail')->with($data2);
    }
}
