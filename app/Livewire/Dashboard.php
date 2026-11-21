<?php

namespace App\Livewire;

use App\Enums\RegistrationIntention;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard | ICHES 2027')]
class Dashboard extends Component
{
    public function render(): View
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $role = $user->role ?? UserRole::User;
        $intention = $user->registration_intention ?? RegistrationIntention::ExplorePrograms;

        $summary = $role === UserRole::Admin ? [
            'accounts' => User::query()->count(),
            'verified' => User::query()->whereNotNull('email_verified_at')->count(),
            'staff' => User::query()->where('role', '!=', UserRole::User->value)->count(),
        ] : null;

        $workspaceRoute = match ($role) {
            UserRole::Admin => 'admin.users',
            UserRole::Reviewer => 'reviewer.workspace',
            UserRole::Committee => 'committee.workspace',
            UserRole::User => 'profile.edit',
        };

        $heroDescription = match ($role) {
            UserRole::Admin => 'Manage accounts, five official programs and event settings from your ICHES workspace.',
            UserRole::Reviewer => 'Your academic review workspace is ready for the upcoming paper review workflow.',
            UserRole::Committee => 'Your operations workspace is ready for the upcoming onsite services workflow.',
            UserRole::User => 'Your personal home for conference participation and the next steps in your ICHES journey.',
        };

        $nextStep = match ($intention) {
            RegistrationIntention::SubmitPaper => [
                'title' => 'Start your paper draft',
                'description' => 'You can now prepare a private paper draft, select one of eight academic tracks, and record the author list. Abstract text, PDF upload and final submission follow in the next phases.',
                'module' => 'ACAD-002 / Draft available',
            ],
            RegistrationIntention::JoinConference => [
                'title' => 'Prepare for conference participation',
                'description' => 'Conference enrollment and online/onsite participation settings will be available when program registration launches.',
                'module' => 'FND-006 / PROG-001',
            ],
            RegistrationIntention::ExplorePrograms => [
                'title' => 'Explore the ICHES programs',
                'description' => 'Five official programs are now configured. Participant enrollment and schedules will be available in the dedicated participation module.',
                'module' => 'PROG-001',
            ],
        };

        return view('livewire.dashboard.index', [
            'user' => $user,
            'role' => $role,
            'intention' => $intention,
            'summary' => $summary,
            'workspaceRoute' => $workspaceRoute,
            'heroDescription' => $heroDescription,
            'nextStep' => $nextStep,
            'canVerifyPayments' => Gate::forUser($user)->allows('verify-payments'),
            'canPublishDecisions' => Gate::forUser($user)->allows('publish-decisions'),
        ]);
    }
}
