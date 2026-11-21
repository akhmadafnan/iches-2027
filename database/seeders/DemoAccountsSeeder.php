<?php

namespace Database\Seeders;

use App\Enums\RegistrationIntention;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('ICHES demo accounts must never be seeded outside local/testing.');
        }

        // One central seeder for every role in LOCAL/TESTING, including Superadmin.
        foreach (UserRole::cases() as $role) {
            $email = $role->value.'@iches.id';

            $existing = User::query()->where('email', $email)->first();

            if ($existing !== null) {
                if ($existing->role !== $role) {
                    throw new RuntimeException('A demo email is already taken by a different role: '.$email);
                }

                // Never reset passwords or overwrite a real user's profile when rerunning.
                // DatabaseSeeder uses WithoutModelEvents, so synchronize the existing primary membership explicitly.
                foreach ($role === UserRole::Superadmin ? UserRole::cases() : [$role] as $membership) {
                    $existing->roleAssignments()->firstOrCreate(['role' => $membership->value]);
                }

                continue;
            }

            $user = new User;
            $user->name = 'ICHES '.$role->label();
            $user->email = $email;
            $user->institution = 'Universitas Islam Syarifuddin';
            $user->country = 'Indonesia';
            $user->registration_intention = RegistrationIntention::ExplorePrograms;
            $user->role = $role;
            // The four ordinary demo accounts retain their original development password.
            // The privileged demo workspace receives a generated local-only credential.
            $generatedRootPassword = $role === UserRole::Superadmin ? Str::random(32) : null;
            $user->password = $generatedRootPassword ?? 'password';
            $user->email_verified_at = Carbon::now();
            $user->profile_setup_required = false;
            $user->save();

            // DatabaseSeeder suppresses Eloquent model events, so explicitly persist role memberships.
            foreach ($role === UserRole::Superadmin ? UserRole::cases() : [$role] as $membership) {
                $user->roleAssignments()->firstOrCreate(['role' => $membership->value]);
            }

            if ($generatedRootPassword !== null && app()->environment('local')) {
                $directory = storage_path('app/private/uat/superadmin');
                File::ensureDirectoryExists($directory);
                File::put($directory.'/credentials.txt', "LOCAL ICHES DEVELOPMENT ACCOUNT\nEmail: {$email}\nPassword: {$generatedRootPassword}\n");
            }
        }
    }
}
