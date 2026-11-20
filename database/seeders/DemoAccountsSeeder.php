<?php

namespace Database\Seeders;

use App\Enums\RegistrationIntention;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('ICHES demo accounts must never be seeded outside local/testing.');
        }

        foreach (UserRole::cases() as $role) {
            $email = $role->value.'@iches.id';

            $existing = User::query()->where('email', $email)->first();

            if ($existing !== null) {
                if ($existing->role !== $role) {
                    throw new RuntimeException('A demo email is already taken by a different role: '.$email);
                }

                // Never reset passwords or overwrite a real user's profile when rerunning.
                continue;
            }

            $user = new User;
            $user->name = 'ICHES '.ucfirst($role->value);
            $user->email = $email;
            $user->institution = 'Universitas Islam Syarifuddin';
            $user->country = 'Indonesia';
            $user->registration_intention = RegistrationIntention::ExplorePrograms;
            $user->role = $role;
            $user->password = 'password';
            $user->email_verified_at = Carbon::now();
            $user->save();
        }
    }
}
