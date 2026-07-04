<?php

namespace Database\Factories;

use App\Models\Toko;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // User tidak memakai trait BelongsToToko (lihat catatan di model),
            // jadi factory perlu mengisi id_toko sendiri. Default ke toko yang
            // sudah ada di DB (bukan Toko::factory() baru) supaya user-user
            // yang dibuat dalam satu test berbagi toko yang sama, selaras
            // dengan asumsi test lama yang single-tenant.
            'id_toko' => Toko::query()->value('id_toko'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static {}
}
