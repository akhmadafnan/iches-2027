<div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1.5">
            <flux:heading size="xl" level="1">Users &amp; Access</flux:heading>
            <flux:text class="max-w-2xl">
                Manage user roles and delegated duties without changing personal account details.
            </flux:text>
        </div>

        <flux:badge color="zinc" size="sm" class="self-start">
            {{ number_format($users->total()) }} {{ \Illuminate\Support\Str::plural('user', $users->total()) }}
        </flux:badge>
    </div>

    @if (session('access_saved'))
        <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('access_saved') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-0.5">
                <flux:heading size="lg" level="2">User directory</flux:heading>
                <flux:text size="sm">Manage role assignments for registered accounts.</flux:text>
            </div>
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search name or email..."
                aria-label="Search users by name or email"
                class="w-full sm:max-w-xs"
            />
        </div>

        <div id="users-table" class="overflow-x-auto">
            <flux:table class="min-w-[680px]">
                <flux:table.columns>
                    <flux:table.column class="w-16">No.</flux:table.column>
                    <flux:table.column>User</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Role</flux:table.column>
                    <flux:table.column align="end">Action</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell class="tabular-nums text-zinc-500 dark:text-zinc-400">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$user->name" />
                                    <div class="min-w-0">
                                        <flux:heading class="font-medium">{{ $user->name }}</flux:heading>
                                        @if ($user->id === auth()->id())
                                            <flux:text size="sm" class="text-zinc-500">Your account</flux:text>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                {{ $user->email }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge
                                    size="sm"
                                    :color="match ($user->role) {
                                        \App\Enums\UserRole::Admin => 'purple',
                                        \App\Enums\UserRole::Reviewer => 'blue',
                                        \App\Enums\UserRole::Committee => 'amber',
                                        default => 'zinc',
                                    }"
                                >
                                    {{ ucfirst($user->role->value) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                @can('updateAccess', $user)
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        icon="pencil-square"
                                        wire:click="edit({{ $user->id }})"
                                    >
                                        Edit access
                                    </flux:button>
                                @else
                                    <flux:text size="sm" class="text-zinc-400">—</flux:text>
                                @endcan
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon.users class="size-6 text-zinc-400" />
                                    <flux:heading>No users found</flux:heading>
                                    <flux:text size="sm">Try a different name or email address.</flux:text>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="flex flex-col gap-3 border-t border-zinc-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-0.5">
                <flux:text size="sm" class="font-medium tabular-nums">
                    Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                </flux:text>
                <flux:text size="sm" class="text-zinc-500">
                    Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                </flux:text>
            </div>
            <div class="flex items-center justify-end gap-2">
                <flux:button
                    type="button"
                    size="sm"
                    variant="outline"
                    icon="chevron-left"
                    wire:click="previousPage"
                    :disabled="$users->onFirstPage()"
                >
                    Previous
                </flux:button>
                <flux:button
                    type="button"
                    size="sm"
                    variant="outline"
                    icon:trailing="chevron-right"
                    wire:click="nextPage"
                    :disabled="! $users->hasMorePages()"
                >
                    Next
                </flux:button>
            </div>
        </div>
    </div>

    <flux:modal wire:model.self="showEditModal" class="md:w-[34rem]">
        @if ($editingUserId !== null)
            <form wire:submit="save" class="space-y-6">
                <div class="space-y-3">
                    <div class="space-y-1">
                        <flux:heading size="lg">Edit user access</flux:heading>
                        <flux:text size="sm">Adjust primary role and delegated access for this account.</flux:text>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:avatar size="md" :name="$editingName" />
                        <div class="min-w-0">
                            <flux:heading class="truncate">{{ $editingName }}</flux:heading>
                            <flux:text size="sm" class="truncate">{{ $editingEmail }}</flux:text>
                        </div>
                    </div>
                </div>

                <flux:select wire:model="role" label="Primary role" required>
                    @foreach ($roles as $availableRole)
                        <flux:select.option :value="$availableRole->value">
                            {{ ucfirst($availableRole->value) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div class="space-y-3">
                    <div class="space-y-1">
                        <flux:heading size="sm">Delegated duties</flux:heading>
                        <flux:text size="sm">These permissions must be explicitly assigned.</flux:text>
                    </div>
                    <flux:checkbox wire:model="finance" label="Finance verification" />
                    <flux:checkbox wire:model="academicAuthority" label="Academic decision authority" />
                    <flux:text size="sm">
                        Finance and Academic Authority require a Committee or Admin role.
                    </flux:text>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button type="button" variant="ghost" wire:click="cancel">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save">
                        Save changes
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
