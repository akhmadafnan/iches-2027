<?php

namespace App\Providers;

use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
    }

    private function configureAuthorization(): void
    {
        Gate::define('manage-users', fn (User $user): bool => $user->role === UserRole::Admin);
        Gate::define('manage-programs', fn (User $user): bool => $user->role === UserRole::Admin);
        Gate::define('manage-event-settings', fn (User $user): bool => $user->role === UserRole::Admin);
        Gate::define('review-papers', fn (User $user): bool => $user->role === UserRole::Reviewer);
        Gate::define('scan-services', fn (User $user): bool => $user->role === UserRole::Committee);
        Gate::define('verify-payments', fn (User $user): bool => in_array($user->role, [UserRole::Committee, UserRole::Admin], true)
            && $user->hasGlobalDuty(StaffDuty::Finance));
        Gate::define('publish-decisions', fn (User $user): bool => in_array($user->role, [UserRole::Committee, UserRole::Admin], true)
            && $user->hasGlobalDuty(StaffDuty::AcademicAuthority));
        Gate::define(
            'moderate-session',
            fn (User $user, int $sessionId): bool => $user->hasScopedDuty(StaffDuty::Moderator, 'session', $sessionId),
        );
        Gate::define(
            'manage-cohost',
            fn (User $user, int $institutionId): bool => $user->hasScopedDuty(StaffDuty::CoHostPic, 'institution', $institutionId),
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
