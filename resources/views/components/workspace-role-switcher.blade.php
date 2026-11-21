@php
    /** @var \App\Models\User $current */
    $current = auth()->user();
    $memberships = $current->assignedRoles();
    $active = $current->activeRole();
@endphp

<div class="mx-3 mb-2 rounded-xl border border-zinc-200 bg-zinc-50/80 p-2 dark:border-zinc-700 dark:bg-zinc-800/50" data-test="workspace-role-switcher">
    <div class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Active workspace</div>
    @if (count($memberships) > 1)
        <flux:dropdown position="top" align="start">
            <flux:button variant="ghost" icon="arrow-path" icon:trailing="chevrons-up-down" class="w-full justify-between">
                {{ $active->label() }}
            </flux:button>
            <flux:menu>
                @foreach ($memberships as $membership)
                    <form method="POST" action="{{ route('workspace.switch-role') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ $membership->value }}" />
                        <flux:menu.item as="button" type="submit" :icon="$active === $membership ? 'check-circle' : 'arrow-right-circle'">
                            {{ $membership->label() }}
                        </flux:menu.item>
                    </form>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    @else
        <div class="px-2 py-1 text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $active->label() }}</div>
    @endif
    <p class="px-2 pt-1 text-[10px] leading-4 text-zinc-500">Same account, role-scoped access.</p>
</div>
