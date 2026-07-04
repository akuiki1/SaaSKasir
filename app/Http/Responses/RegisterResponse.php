<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Selalu ke onboarding (bukan dashboard biasa) — registrasi mandiri hanya
     * menghasilkan admin toko baru, yang butuh dituntun impor produk dulu.
     */
    public function toResponse($request): Response
    {
        return redirect()->route('admin.onboarding');
    }
}
