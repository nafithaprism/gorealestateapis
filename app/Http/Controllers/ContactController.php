<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    private const ADMIN_EMAIL = 'info@GoGroupInvest.com';
    private const LOGO_URL    = 'https://gorealestate.b-cdn.net/Gallery/clrlogo.png';

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name'            => ['required','string','max:150'],
            'mobile_number'        => ['nullable','string','max:30'],
            'country_of_residency' => ['nullable','string','max:120'],
            'nationality'          => ['nullable','string','max:120'],
            'email'                => ['nullable','email','max:255'],
            'referral_source'      => ['nullable','string','max:150'],
            'belongs_to'           => ['required', Rule::in(['realestate','client'])],
        ]);

        $contact = Contact::create($data);

        // Email notifications
        $details = [
            'Full Name'            => $contact->full_name,
            'Email'                => $contact->email ?? '—',
            'Mobile Number'        => $contact->mobile_number ?? '—',
            'Country of Residency' => $contact->country_of_residency ?? '—',
            'Nationality'          => $contact->nationality ?? '—',
            'Referral Source'      => $contact->referral_source ?? '—',
            'Belongs To'           => ucfirst($contact->belongs_to),
            'Created At'           => $contact->created_at->toDateTimeString(),
        ];

        $adminHtml = view('emails.brand', [
            'logo_url'    => self::LOGO_URL,
            'preheader'   => 'A new contact has been created.',
            'title'       => 'New Contact Created',
            'intro'       => "<p>You received a new contact.</p>",
            'details'     => $details,
            'cta_url'     => url('/admin/contacts/'.$contact->id), // adjust if you have an admin panel
            'cta_label'   => 'View Contact',
            'footer_text' => 'GO Group Invest · Dubai, UAE',
        ])->render();

        $userHtml = view('emails.brand', [
            'logo_url'    => self::LOGO_URL,
            'preheader'   => 'We received your message',
            'title'       => 'Thanks for contacting us',
            'intro'       => "<p>Hi <strong>{$contact->full_name}</strong>, we’ve received your details and will get back to you soon.</p>",
            'details'     => [
                'Full Name'  => $contact->full_name,
                'Belongs To' => ucfirst($contact->belongs_to),
            ] + ($contact->email ? ['Email' => $contact->email] : []),
            'footer_text' => 'GO Group Invest · Dubai, UAE',
        ])->render();

        try {
            Mail::html($adminHtml, function ($m) use ($contact) {
                $m->to(self::ADMIN_EMAIL)->subject('New Contact: '.$contact->full_name);
            });

            if (!empty($contact->email)) {
                Mail::html($userHtml, function ($m) use ($contact) {
                    $m->to($contact->email)->subject('We received your message');
                });
            }
        } catch (\Throwable $e) {
            // \Log::warning('Contact email send failed: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Contact created',
            'data'    => [
                'id'         => $contact->id,
                'full_name'  => $contact->full_name,
                'email'      => $contact->email,
                'belongs_to' => $contact->belongs_to,
                'created_at' => $contact->created_at,
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q'          => ['nullable','string','max:150'],
            'belongs_to' => ['nullable','in:realestate,client'],
            'per_page'   => ['nullable','integer','min:1','max:100'],
            'page'       => ['nullable','integer','min:1'],
            'sort'       => ['nullable','in:created_at,-created_at,full_name,-full_name'],
        ]);

        $perPage = $data['per_page'] ?? 15;
        $sort    = $data['sort'] ?? '-created_at';

        $query = Contact::query();

        if (!empty($data['belongs_to'])) {
            $query->where('belongs_to', $data['belongs_to']);
        }

        if (!empty($data['q'])) {
            $q = $data['q'];
            $query->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('mobile_number', 'like', "%{$q}%");
            });
        }

        // Sorting
        if ($sort[0] === '-') {
            $query->orderBy(ltrim($sort, '-'), 'desc');
        } else {
            $query->orderBy($sort, 'asc');
        }

        $contacts = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => $contacts->items(),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'per_page'     => $contacts->perPage(),
                'total'        => $contacts->total(),
                'last_page'    => $contacts->lastPage(),
                'has_more'     => $contacts->hasMorePages(),
            ],
            'links' => [
                'first' => $contacts->url(1),
                'prev'  => $contacts->previousPageUrl(),
                'next'  => $contacts->nextPageUrl(),
                'last'  => $contacts->url($contacts->lastPage()),
            ],
        ]);
    }
}