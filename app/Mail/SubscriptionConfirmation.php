<?php

// app/Mail/SubscriptionConfirmation.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $fullName;

    public function __construct($fullName)
    {
        $this->fullName = $fullName;
    }

    public function build()
    {
        return $this->subject('Subscription Confirmation')
                    ->view('emails.subscription')
                    ->with([
                        'fullName' => $this->fullName
                    ]);
    }
}