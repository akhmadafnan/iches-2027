<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f6f8f7] dark:bg-zinc-950">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Overview')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @canany(['manage-users', 'review-papers', 'scan-services'])
                    <flux:sidebar.group :heading="__('Workspace')" class="grid">
                    @can('manage-users')
                        <flux:sidebar.item icon="squares-2x2" :href="route('admin.programs')" :current="request()->routeIs('admin.programs')" wire:navigate>{{ __('Programs') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="book-open" :href="route('admin.tracks')" :current="request()->routeIs('admin.tracks')" wire:navigate>{{ __('Conference Tracks') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="adjustments-horizontal" :href="route('admin.event-settings')" :current="request()->routeIs('admin.event-settings')" wire:navigate>{{ __('Event Settings') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                            {{ __('Users & Access') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('review-papers')
                        <flux:sidebar.item icon="book-open" :href="route('reviewer.workspace')" :current="request()->routeIs('reviewer.workspace')" wire:navigate>
                            {{ __('Reviewer Workspace') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('scan-services')
                        <flux:sidebar.item icon="qr-code" :href="route('committee.workspace')" :current="request()->routeIs('committee.workspace')" wire:navigate>
                            {{ __('Committee Workspace') }}
                        </flux:sidebar.item>
                    @endcan
                    </flux:sidebar.group>
                @endcanany
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('My Account')" class="grid">
                    <flux:sidebar.item icon="user-circle" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                        {{ __('My Profile') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shield-check" :href="route('security.edit')" :current="request()->routeIs('security.edit')" wire:navigate>
                        {{ __('Security') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                <p class="px-4 pb-2 text-[11px] font-medium text-zinc-400 dark:text-zinc-500">
                    ICHES 2027 · UNISYA
                </p>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    :avatar="auth()->user()->profile_photo_path ? route('profile.photo') : null"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                    :src="auth()->user()->profile_photo_path ? route('profile.photo') : null"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
