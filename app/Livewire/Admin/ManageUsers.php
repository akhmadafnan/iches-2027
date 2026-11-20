<?php

namespace App\Livewire\Admin;

use App\Actions\Access\UpdateUserAccess;
use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Models\StaffAssignment;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manage users & access')]
class ManageUsers extends Component
{
    use WithPagination;

    public ?int $editingUserId = null;

    public string $editingName = '';

    public string $editingEmail = '';

    public bool $showEditModal = false;

    public string $search = '';

    public string $roleFilter = 'all';

    public string $role = 'user';

    public bool $finance = false;

    public bool $academicAuthority = false;

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function edit(int $userId): void
    {
        $target = User::query()->findOrFail($userId);
        Gate::authorize('updateAccess', $target);

        $this->editingUserId = $target->id;
        $this->editingName = $target->name;
        $this->editingEmail = $target->email;
        $this->role = $target->role->value;
        $this->finance = $target->hasGlobalDuty(StaffDuty::Finance);
        $this->academicAuthority = $target->hasGlobalDuty(StaffDuty::AcademicAuthority);
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function cancel(): void
    {
        $this->editingUserId = null;
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function save(UpdateUserAccess $updateUserAccess): void
    {
        Gate::authorize('viewAny', User::class);

        $validated = $this->validate([
            'editingUserId' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'finance' => ['boolean'],
            'academicAuthority' => ['boolean'],
        ]);

        $target = User::query()->findOrFail((int) $validated['editingUserId']);
        Gate::authorize('updateAccess', $target);

        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        $updateUserAccess->execute(
            $actor,
            $target,
            UserRole::from($validated['role']),
            $validated['finance'],
            $validated['academicAuthority'],
        );

        $this->editingUserId = null;
        $this->showEditModal = false;
        Flux::toast(variant: 'success', text: 'User access updated successfully.');
    }

    public function render(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('livewire.admin.manage-users', [
            'users' => User::query()
                ->when(trim($this->search) !== '', function (Builder $query): void {
                    $term = '%'.trim($this->search).'%';

                    $query->where(function (Builder $filter) use ($term): void {
                        $filter->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
                })
                ->when($this->roleFilter !== 'all', fn (Builder $query) => $query->where('role', $this->roleFilter))
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(10),
            'roles' => UserRole::cases(),
            'totalAccounts' => User::query()->count(),
            'staffAccounts' => User::query()->where('role', '!=', UserRole::User->value)->count(),
            'delegatedDuties' => StaffAssignment::query()->count(),
        ]);
    }
}
