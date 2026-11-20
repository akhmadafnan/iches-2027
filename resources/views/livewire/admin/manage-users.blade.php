<div class="space-y-6">
    <div>
        <flux:heading size="xl">Users &amp; Access</flux:heading>
        <flux:text class="mt-2">Assign one primary role per person. Finance and Academic Authority are separate delegated duties.</flux:text>
    </div>

    @if (session('access_saved'))
        <div role="status" class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
            {{ session('access_saved') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                <tr>
                    <th class="px-4 py-3 font-semibold">Name</th>
                    <th class="px-4 py-3 font-semibold">Email</th>
                    <th class="px-4 py-3 font-semibold">Role</th>
                    <th class="px-4 py-3 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ ucfirst($user->role->value) }}</td>
                        <td class="px-4 py-3">
                            @can('updateAccess', $user)
                                <flux:button size="sm" wire:click="edit({{ $user->id }})">Edit access</flux:button>
                            @else
                                <flux:text class="text-xs">Current account</flux:text>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}

    @if ($editingUserId !== null)
        <form wire:submit="save" class="max-w-lg space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">Edit user access</flux:heading>
            <flux:select wire:model="role" label="Primary role" required>
                @foreach ($roles as $availableRole)
                    <flux:select.option :value="$availableRole->value">{{ ucfirst($availableRole->value) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:checkbox wire:model="finance" label="Finance verification assignment" />
            <flux:checkbox wire:model="academicAuthority" label="Academic decision authority" />
            <flux:text class="text-xs">Delegated duties require Committee or Admin primary role. Moderator and Co-Host PIC scopes will be assigned after sessions and institutions are created.</flux:text>
            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Save access</flux:button>
                <flux:button type="button" wire:click="cancel" variant="ghost">Cancel</flux:button>
            </div>
            @error('role') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror
        </form>
    @endif
</div>
