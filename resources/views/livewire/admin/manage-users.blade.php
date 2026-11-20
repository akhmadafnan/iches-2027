<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    {{-- Page identity --}}
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-7 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-8">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-36 size-96 rounded-full border border-white/10 bg-emerald-400/10 blur-2xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-44 right-36 size-72 rounded-full bg-emerald-300/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200">
                    <flux:icon.shield-check class="size-4" />
                    ICHES 2027 / Administration
                </div>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">Users &amp; Access</h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-emerald-50/80">
                    A secure workspace to manage people, assign roles, and delegate responsibilities.
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-2 self-start rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-medium text-emerald-50 backdrop-blur-sm sm:self-end">
                <span aria-hidden="true" class="size-2 rounded-full bg-emerald-300 shadow-[0_0_0_4px_rgba(110,231,183,.12)]"></span>
                Access control enabled
            </div>
        </div>
    </section>

    {{-- Live data snapshots --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                <flux:icon.users class="size-6" />
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total accounts</p>
                <p class="mt-0.5 text-[25px] font-semibold leading-none tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ number_format($totalAccounts) }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400">
                <flux:icon.identification class="size-6" />
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Staff accounts</p>
                <p class="mt-0.5 text-[25px] font-semibold leading-none tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ number_format($staffAccounts) }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400">
                <flux:icon.key class="size-6" />
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Delegated duties</p>
                <p class="mt-0.5 text-[25px] font-semibold leading-none tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ number_format($delegatedDuties) }}</p>
            </div>
        </div>
    </div>

    {{-- Directory --}}
    <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-5 border-b border-zinc-100 px-6 py-6 dark:border-zinc-800 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">User directory</h2>
                    <flux:badge color="emerald" size="sm">{{ number_format($users->total()) }} results</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Review user information and manage role-based access.
                </p>
            </div>

            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Search name or email"
                    aria-label="Search users by name or email"
                    class="w-full sm:w-[260px]"
                />
                <flux:select wire:model.live="roleFilter" aria-label="Filter users by role" class="w-full sm:w-40">
                    <flux:select.option value="all">All roles</flux:select.option>
                    @foreach ($roles as $availableRole)
                        <flux:select.option :value="$availableRole->value">{{ ucfirst($availableRole->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="flex items-center gap-1.5 px-6 pt-3 text-xs text-zinc-500 dark:text-zinc-400 md:hidden">
            <flux:icon.arrows-right-left class="size-3.5" /> Swipe the table to see all columns
        </div>

        <div id="users-table" class="overflow-x-auto px-5 pb-1">
            <flux:table class="w-full min-w-[780px]">
                <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-800/50">
                    <flux:table.column class="w-16 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">No.</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">User</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Email</flux:table.column>
                    <flux:table.column class="w-40 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Role</flux:table.column>
                    <flux:table.column align="end" class="w-44 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($users as $user)
                        <flux:table.row :key="$user->id" class="transition-colors hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                            <flux:table.cell class="!py-4 text-sm font-medium tabular-nums text-zinc-400 dark:text-zinc-500">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <div class="flex min-w-0 items-center gap-3.5">
                                    <flux:avatar size="md" :name="$user->name" class="ring-1 ring-zinc-200 dark:ring-zinc-700" />
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                            @if ($user->id === auth()->id())
                                                <span class="inline-flex shrink-0 items-center rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">You</span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $user->institution ?: 'No institution recorded' }}
                                        </p>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <span class="whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300">{{ $user->email }}</span>
                            </flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <flux:badge
                                    size="sm"
                                    :color="match ($user->role) {
                                        \App\Enums\UserRole::Admin => 'emerald',
                                        \App\Enums\UserRole::Reviewer => 'blue',
                                        \App\Enums\UserRole::Committee => 'amber',
                                        default => 'zinc',
                                    }"
                                >
                                    {{ ucfirst($user->role->value) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end" class="!py-4">
                                @can('updateAccess', $user)
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        icon="pencil-square"
                                        wire:click="edit({{ $user->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="edit({{ $user->id }})"
                                    >
                                        Edit access
                                    </flux:button>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-400">
                                        <flux:icon.lock-closed class="size-3.5" /> Protected
                                    </span>
                                @endcan
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="!py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5">
                                    <div class="flex size-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                        <flux:icon.users class="size-6" />
                                    </div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">No users found</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Try adjusting your search or role filter.</p>
                                    <flux:button type="button" variant="ghost" size="sm" wire:click="$set('search', '')" wire:loading.attr="disabled">Clear search</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Fixed pagination contract: status on left, controls on right --}}
        <div class="flex flex-col gap-3 border-t border-zinc-100 bg-zinc-50/65 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                <span class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-100">
                    Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                </span>
                <span class="hidden text-zinc-300 sm:inline">|</span>
                <span class="tabular-nums text-zinc-500 dark:text-zinc-400">
                    Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}
                </span>
            </div>
            <div class="flex items-center justify-end gap-2">
                <flux:button type="button" size="sm" variant="outline" icon="chevron-left" wire:click="previousPage" :disabled="$users->onFirstPage()">
                    Previous
                </flux:button>
                <flux:button type="button" size="sm" variant="outline" icon:trailing="chevron-right" wire:click="nextPage" :disabled="! $users->hasMorePages()">
                    Next
                </flux:button>
            </div>
        </div>
    </section>

    <flux:modal wire:model.self="showEditModal" class="md:w-[35rem]">
        @if ($editingUserId !== null)
            <form wire:submit="save" class="space-y-6">
                <div class="space-y-2">
                    <div class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                        <flux:icon.shield-check class="size-6" />
                    </div>
                    <flux:heading size="xl">Edit access permissions</flux:heading>
                    <flux:text size="sm">Changes to roles and delegated duties apply immediately when saved.</flux:text>
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:avatar size="md" :name="$editingName" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $editingName }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $editingEmail }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:select wire:model="role" label="Primary role" required>
                        @foreach ($roles as $availableRole)
                            <flux:select.option :value="$availableRole->value">{{ ucfirst($availableRole->value) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <p class="text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                        The primary role controls access to Admin, Reviewer, or Committee workspaces.
                    </p>
                </div>

                <div class="space-y-3 rounded-xl border border-zinc-200 px-4 py-4 dark:border-zinc-700">
                    <div class="space-y-1">
                        <flux:heading size="sm">Additional responsibilities</flux:heading>
                        <flux:text size="sm">Grant only when explicitly delegated.</flux:text>
                    </div>
                    <flux:checkbox wire:model="finance" label="Finance verification" />
                    <flux:checkbox wire:model="academicAuthority" label="Academic decision authority" />
                    <p class="text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                        These duties require a Committee or Admin primary role.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    <flux:button type="button" variant="ghost" wire:click="cancel">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save">Save changes</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
