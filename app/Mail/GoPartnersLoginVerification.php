<?php

// app/Mail/GoPartnersLoginVerification.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GoPartnersLoginVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $partner;

    public function __construct($partner)
    {
        $this->partner = $partner;
    }

    public function build()
    {
        return $this->subject('GoPartners - Email Verification')
            ->view('emails.go_partners_login_verification')
            ->with([
                'code' => $this->partner->email_verification_code,
                'name' => $this->partner->first_name . ' ' . $this->partner->last_name
            ]);
    }
}
