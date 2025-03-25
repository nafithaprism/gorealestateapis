<?php


// app/Models/GoPartnersLogin.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class GoPartnersLogin extends Model
{
    protected $table = 'go_partners_logins';
    use HasApiTokens;

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
