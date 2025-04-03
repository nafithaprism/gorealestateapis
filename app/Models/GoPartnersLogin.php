<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;

class GoPartnersLogin extends Model
{
    use HasApiTokens, CanResetPassword;

    protected $table = 'go_partners_logins';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'email_verified',
        'email_verification_code',
        'phone_verified',
        'phone_verification_code',
        'document_url',
        'status'
    ];

    protected $hidden = [
        'password',
        'email_verification_code',
        'phone_verification_code'
    ];
}