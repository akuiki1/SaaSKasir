<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        return redirect()->intended(match (Auth::user()?->role) {
            'ceo' => '/ceo/dashboard',
            'superadmin' => '/superadmin/dashboard',
            'admin' => '/admin/dashboard',
            default => '/kasir/dashboard',
        });
    }
}
