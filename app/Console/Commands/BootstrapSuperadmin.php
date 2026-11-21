<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class BootstrapSuperadmin extends Command
{
    protected $signature = 'access:bootstrap-superadmin {email} {--confirm : Explicit confirmation for one-time LOCAL bootstrap}';

    protected $description = 'Grant the first Superadmin role to an existing verified account in LOCAL development only';

    public function handle(): int
    {
        if (! app()->environment('local', 'testing') || ! $this->option('confirm')) {
            $this->error('Local development only. Provide --confirm explicitly. Never run as a production seeder.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user || ! $user->hasVerifiedEmail()) {
            $this->error('The specified account must exist and have a verified email.');

            return self::FAILURE;
        }

        // Hard-stop known seeded/demo identities and the default demo password.
        if (str_ends_with($email, '@iches.id') || Hash::check('password', $user->password)) {
            $this->error('Demo accounts or known default passwords cannot be promoted. Use a verified personal account with a secure password.');

            return self::FAILURE;
        }

        if (DB::table('user_roles')->where('role', UserRole::Superadmin->value)->exists()) {
            $this->error('A Superadmin already exists. Further assignments require an authenticated Superadmin.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($user): void {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            // First root receives every existing workspace role, enabling immediate safe role switching.
            foreach (UserRole::cases() as $membership) {
                $user->roleAssignments()->firstOrCreate(['role' => $membership->value]);
            }
            $user->role = UserRole::Superadmin;
            $user->save();
        });

        Log::warning('ICHES first Superadmin bootstrapped locally', ['target_user_id' => $user->id]);
        $this->info('First local Superadmin assigned. Sign out and sign in again to activate.');

        return self::SUCCESS;
    }
}
