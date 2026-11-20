<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    {{-- Role-specific introduction, consistent with the approved Flux emerald UI --}}
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-10 sm:py-10">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-36 size-96 rounded-full border border-white/10 bg-emerald-400/10 blur-2xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-44 right-36 size-72 rounded-full bg-emerald-300/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-7 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <div class="mb-4 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200">
                    <flux:icon.squares-2x2 class="size-4" />
                    ICHES 2027 / {{ match ($role) {
                        \App\Enums\UserRole::Admin => 'Administration',
                        \App\Enums\UserRole::Reviewer => 'Academic Review',
                        \App\Enums\UserRole::Committee => 'Committee Operations',
                        default => 'Participant Portal',
                    } }}
                </div>

                <h1 class="text-3xl font-semibold tracking-tight sm:text-[35px]">
                    Welcome back, {{ $user->name }}.
                </h1>
                <p class="mt-3 max-w-xl text-sm leading-7 text-emerald-50/85">
                    {{ $heroDescription }}
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <flux:button
                        :href="route($workspaceRoute)"
                        variant="primary"
                        icon:trailing="arrow-up-right"
                        class="!bg-white !text-emerald-950 hover:!bg-emerald-50"
                        wire:navigate
                    >
                        {{ match ($role) {
                            \App\Enums\UserRole::Admin => 'Manage users & access',
                            \App\Enums\UserRole::Reviewer => 'Open reviewer workspace',
                            \App\Enums\UserRole::Committee => 'Open committee workspace',
                            default => 'Review my profile',
                        } }}
                    </flux:button>
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-emerald-100/85">
                        <span class="size-1.5 rounded-full bg-emerald-300"></span>
                        Your personal workspace
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-3 self-start rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm md:self-end">
                <flux:avatar
                    size="lg"
                    :name="$user->name"
                    :src="$user->profile_photo_path ? route('profile.photo') : null"
                    class="ring-2 ring-white/25"
                />
                <div class="min-w-0">
                    <p class="max-w-44 truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                    <p class="mt-0.5 text-xs text-emerald-100/85">{{ ucfirst($role->value) }} access</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Real, role-aware information. No simulated paper, payment or program counts. --}}
    <section aria-label="Account overview" class="grid gap-4 sm:grid-cols-3">
        @if ($role === \App\Enums\UserRole::Admin)
            <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                    <flux:icon.users class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Registered accounts</p>
                    <p class="mt-1 text-[26px] font-semibold leading-none tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ number_format($summary['accounts']) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400">
                    <flux:icon.check-badge class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Verified emails</p>
                    <p class="mt-1 text-[26px] font-semibold leading-none tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ number_format($summary['verified']) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700 dark:bg-violet-950 dark:text-violet-400">
                    <flux:icon.shield-check class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Staff accounts</p>
                    <p class="mt-1 text-[26px] font-semibold leading-none tracking-tight tabular-nums text-zinc-900 dark:text-white">{{ number_format($summary['staff']) }}</p>
                </div>
            </div>
        @else
            <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                    <flux:icon.shield-check class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Account role</p>
                    <p class="mt-1 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ ucfirst($role->value) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400">
                    <flux:icon.envelope class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Email status</p>
                    <p class="mt-1 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $user->hasVerifiedEmail() ? 'Verified' : 'Pending' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-[0_3px_14px_-7px_rgba(15,23,42,.12)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400">
                    <flux:icon.camera class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Profile photo</p>
                    <p class="mt-1 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $user->profile_photo_path ? 'On file' : 'Not uploaded' }}</p>
                </div>
            </div>
        @endif
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.8fr)_minmax(300px,1fr)]">
        <div class="min-w-0 space-y-5">
            <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 px-6 py-5 dark:border-zinc-800">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">
                            {{ $role === \App\Enums\UserRole::User ? 'Your ICHES journey' : 'Your workspace' }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $role === \App\Enums\UserRole::User ? 'Guidance based on your registration intention.' : 'Available actions for your current access level.' }}
                        </p>
                    </div>
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                        <flux:icon.arrow-up-right class="size-5" />
                    </div>
                </div>

                @if ($role === \App\Enums\UserRole::User)
                    <div class="space-y-5 px-6 py-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Your registration intention</span>
                            <flux:badge color="emerald">{{ $intention->label() }}</flux:badge>
                        </div>

                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-5 dark:border-emerald-900 dark:bg-emerald-950/30">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0 flex-1 space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Next step</p>
                                    <h3 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $nextStep['title'] }}</h3>
                                    <p class="max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $nextStep['description'] }}</p>
                                </div>
                                <flux:badge color="amber" size="sm" class="shrink-0 self-start">Coming soon</flux:badge>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-emerald-200/70 pt-4 dark:border-emerald-900">
                                <flux:button :href="route('profile.edit')" variant="primary" icon="pencil-square" size="sm" wire:navigate>Review my profile</flux:button>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Planned module: {{ $nextStep['module'] }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Your participation journey</h3>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="flex items-start gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                                        <flux:icon.check class="size-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Account ready</p>
                                        <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">Your ICHES profile is registered and accessible.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800">
                                        <flux:icon.calendar-days class="size-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Program participation</p>
                                        <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">Enrollment and submission services are being prepared.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-5 px-6 py-6">
                        <div class="flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                                @if ($role === \App\Enums\UserRole::Admin)
                                    <flux:icon.users class="size-6" />
                                @elseif ($role === \App\Enums\UserRole::Reviewer)
                                    <flux:icon.book-open class="size-6" />
                                @else
                                    <flux:icon.clipboard-document-check class="size-6" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">{{ match ($role) {
                                        \App\Enums\UserRole::Admin => 'Manage users & access',
                                        \App\Enums\UserRole::Reviewer => 'Reviewer workspace',
                                        default => 'Committee workspace',
                                    } }}</h3>
                                    <flux:badge color="emerald" size="sm">Available</flux:badge>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                    {{ match ($role) {
                                        \App\Enums\UserRole::Admin => 'View registered accounts, change other users’ roles, and assign explicit staff responsibilities.',
                                        \App\Enums\UserRole::Reviewer => 'Open your role-protected workspace. Review queue and self-claim features will arrive with the academic review module.',
                                        default => 'Open your role-protected workspace. QR scanning and service operations will arrive in the dedicated QR workstream.',
                                    } }}
                                </p>
                                <flux:button :href="route($workspaceRoute)" variant="primary" icon:trailing="arrow-up-right" class="mt-4" wire:navigate>
                                    {{ $role === \App\Enums\UserRole::Admin ? 'Open users & access' : 'Open workspace' }}
                                </flux:button>
                            </div>
                        </div>

                        @if ($canVerifyPayments || $canPublishDecisions)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                                <p class="mb-2 text-sm font-semibold text-emerald-900 dark:text-emerald-200">Your delegated duties</p>
                                <div class="flex flex-wrap gap-2">
                                    @if ($canVerifyPayments)
                                        <flux:badge color="emerald">Finance verification</flux:badge>
                                    @endif
                                    @if ($canPublishDecisions)
                                        <flux:badge color="emerald">Academic decision authority</flux:badge>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs leading-5 text-emerald-800 dark:text-emerald-300">Assignment recorded. Dedicated finance and academic decision actions are not yet available.</p>
                            </div>
                        @endif

                        <div class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-950/30">
                            <flux:icon.clock class="mt-0.5 size-4 shrink-0 text-zinc-500" />
                            <div>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Next development phase</p>
                                <p class="mt-1 text-xs leading-5 text-zinc-600 dark:text-zinc-400">
                                    {{ match ($role) {
                                        \App\Enums\UserRole::Admin => 'Program management, conference tracks, and event configuration will follow in FND-006 and ACAD-001.',
                                        \App\Enums\UserRole::Reviewer => 'Available submissions, atomic self-claim, and structured review workflow will follow in REV-001–003.',
                                        default => 'Service configuration and mobile QR scanning will follow in the QRS modules.',
                                    } }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-zinc-200/90 bg-white px-6 py-5 shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">ICHES 2027</h2>
                        <p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            The 6th International Conference on Humanity, Education, and Society
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                        <flux:icon.calendar-days class="size-4" /> 5–6 January 2027
                    </div>
                </div>
            </section>
        </div>

        <aside class="min-w-0 space-y-5">
            <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-6 py-5 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">My account</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Your personal ICHES identity.</p>
                </div>
                <div class="space-y-4 px-6 py-6">
                    <div class="flex items-center gap-3">
                        <flux:avatar size="lg" :name="$user->name" :src="$user->profile_photo_path ? route('profile.photo') : null" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 border-t border-zinc-100 pt-4 text-sm dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-3">
                            <span class="shrink-0 text-zinc-500 dark:text-zinc-400">Institution</span>
                            <span class="text-right font-medium text-zinc-800 dark:text-zinc-200">{{ $user->institution ?: 'Not provided' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-zinc-500 dark:text-zinc-400">Country</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $user->country ?: 'Not provided' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-zinc-500 dark:text-zinc-400">Email</span>
                            <flux:badge :color="$user->hasVerifiedEmail() ? 'emerald' : 'amber'" size="sm">
                                {{ $user->hasVerifiedEmail() ? 'Verified' : 'Pending' }}
                            </flux:badge>
                        </div>
                    </div>

                    @if (! $user->profile_photo_path)
                        <div class="rounded-lg bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                            Add a profile photo for your ICHES identity.
                        </div>
                    @endif

                    <flux:button :href="route('profile.edit')" variant="outline" icon="pencil-square" class="w-full" wire:navigate>
                        Edit profile
                    </flux:button>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-200/90 bg-white px-6 py-5 shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-start gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <flux:icon.lock-closed class="size-4" />
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Account security</h2>
                        <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">Manage your password and two-factor authentication.</p>
                        <flux:link :href="route('security.edit')" class="mt-3 inline-block text-sm font-medium" wire:navigate>Security settings</flux:link>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
