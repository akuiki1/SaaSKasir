<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use App\Services\RegistrasiService;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private readonly RegistrasiService $service) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, [
            'nama_toko' => ['required', 'string', 'max:255'],
            'nama_pemilik' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,}$/'],
            'password' => $this->passwordRules(),
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid.',
        ])->validate();

        return $this->service->daftarkanToko($validated);
    }
}
