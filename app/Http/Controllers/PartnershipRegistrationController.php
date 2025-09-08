<?php

namespace App\Http\Controllers;

use App\Models\PartnershipRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class PartnershipRegistrationController extends Controller
{
    private const ADMIN_EMAIL = 'info@GoGroupInvest.com';
    private const PARTNERSHIP_VALUE_USD = 50000;
    private const LOGO_URL = 'https://gorealestate.b-cdn.net/Gallery/clrlogo.png';

    public function store(Request $request): JsonResponse
    {
        // Validate input
        $data = $request->validate([
            'full_name'            => ['required','string','max:150'],
            'mobile_number'        => ['required','string','max:30'],
            'country_of_residency' => ['required','string','max:120'],
            'nationality'          => ['required','string','max:120'],
            'email'                => ['required','email','max:255'],
            'referral_source'      => ['nullable','string','max:150'],
            'payment_option'       => ['required','in:full_payment,payment_plan'],
        ]);

        // Save
        $registration = PartnershipRegistration::create($data);

        // ------- REPLACE your old email block with everything from here ↓ -------

        // Prepare a "details" list for the email template
        $details = [
            'Full Name'             => $data['full_name'],
            'Mobile Number'         => $data['mobile_number'],
            'Country of Residency'  => $data['country_of_residency'],
            'Nationality'           => $data['nationality'],
            'Email'                 => $data['email'],
            'Referral Source'       => $data['referral_source'] ?? '—',
            'Payment Option'        => $data['payment_option'] === 'full_payment' ? 'Full Payment' : 'Payment Plan',
            'Partnership Value'     => 'USD $'.number_format(self::PARTNERSHIP_VALUE_USD),
        ];

        // Admin notification (uses logo template)
        $adminHtml = view('emails.brand', [
            'logo_url'    => self::LOGO_URL,
            'preheader'   => 'New GO Business Partnership registration',
            'title'       => 'New Partnership Registration',
            'intro'       => "<p>You’ve received a new submission.</p>",
            'details'     => $details,
            'cta_url'     => 'https://gogroupinvest.com/admin',
            'cta_label'   => 'Open Admin',
            'footer_text' => 'GO Group Invest · Dubai, UAE',
        ])->render();

        // Applicant confirmation (optional)
        $applicantHtml = view('emails.brand', [
            'logo_url'    => self::LOGO_URL,
            'preheader'   => 'We received your registration',
            'title'       => 'Registration Received',
            'intro'       => "<p>Thanks, <strong>{$data['full_name']}</strong>! "
                            . "We received your registration for the <strong>GO Business Partnership</strong>. "
                            . "Our team will contact you shortly.</p>",
            'details'     => [
                'Email'             => $data['email'],
                'Payment Option'    => $data['payment_option'] === 'full_payment' ? 'Full Payment' : 'Payment Plan',
                'Partnership Value' => 'USD $'.number_format(self::PARTNERSHIP_VALUE_USD),
            ],
            'footer_text' => 'GO Group Invest · Dubai, UAE',
        ])->render();

        try {
            // Send admin mail
            Mail::html($adminHtml, function ($message) use ($data) {
                $message->to(self::ADMIN_EMAIL)
                        ->subject('GO Business Partnership Registration - ' . $data['full_name']);
            });

            // Send applicant confirmation
            Mail::html($applicantHtml, function ($message) use ($data) {
                $message->to($data['email'])
                        ->subject('We received your GO Business Partnership registration');
            });
        } catch (\Throwable $e) {
            // Don’t fail the API if email has a transient issue
        }

        // ------- REPLACE block ends here ↑ -------

        return response()->json([
            'message' => 'Registration received',
            'id'      => $registration->id,
        ], 201);
    }
}
