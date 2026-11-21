<?php

namespace App\Livewire;

use App\Enums\PaperStatus;
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
        $ownedPaper = $intention === RegistrationIntention::SubmitPaper ? $user->papers()->first() : null;

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
            RegistrationIntention::SubmitPaper => $ownedPaper === null ? [
                'title' => 'Start your paper draft',
                'description' => 'Create your one conference paper and complete its title, track, authors, abstract, keywords and PDF before submission.',
                'module' => 'ACAD-005 / One owned submission',
            ] : ($ownedPaper->status === PaperStatus::Draft ? [
                'title' => 'Continue your paper draft',
                'description' => "Continue editing {$ownedPaper->reference}. Your permanent Paper ID stays the same when you submit.",
                'module' => 'ACAD-005 / Draft in progress',
            ] : (in_array($ownedPaper->status, [PaperStatus::Accepted, PaperStatus::Rejected], true) ? [
                'title' => 'Academic decision published: '.ucfirst($ownedPaper->status->value),
                'description' => "Paper {$ownedPaper->reference} has a published academic decision. Open your submission to see the decision date and approved General Comments.",
                'module' => 'DEC-BULK-001 / Published decision',
            ] : [
                'title' => 'Track your submitted paper',
                'description' => "Paper {$ownedPaper->reference} has been submitted. Follow its review progress in My Submissions; no second paper can be created.",
                'module' => 'ACAD-005 / Submitted paper',
            ])),
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
            'ownedPaper' => $ownedPaper,
            'canVerifyPayments' => Gate::forUser($user)->allows('verify-payments'),
            'canPublishDecisions' => Gate::forUser($user)->allows('publish-decisions'),
        ]);
    }
}
