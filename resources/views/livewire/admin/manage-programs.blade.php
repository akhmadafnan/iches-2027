<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-32 size-96 rounded-full border border-white/10 bg-emerald-400/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200">
                    <flux:icon.squares-2x2 class="size-4" />
                    ICHES 2027 / Administration
                </p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">Program Management</h1>
                <p class="mt-2 max-w-2xl text-sm leading-7 text-emerald-50/85">
                    Manage the five official activities in one place, with names, dates, venues and participation formats sourced from the ICHES TOR.
                </p>
            </div>
            <flux:button :href="route('admin.event-settings')" variant="primary" class="!bg-white !text-emerald-950 hover:!bg-emerald-50" icon:trailing="arrow-up-right" wire:navigate>
                Event Settings
            </flux:button>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.squares-2x2 class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Programs configured</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $totalPrograms }}</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400"><flux:icon.document-text class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Official reference</p><p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">ICHES 2027 TOR</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400"><flux:icon.shield-check class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Identifiers</p><p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">Protected / Stable</p></div>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-5 border-b border-zinc-100 px-6 py-6 dark:border-zinc-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Official program directory</h2>
                    <flux:badge color="emerald" size="sm">{{ $programs->total() }} results</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Five core programs; edit details without changing their permanent identifiers.</p>
            </div>
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search programs or venue" aria-label="Search programs or venue" class="w-full lg:w-[290px]" />
        </div>

        <div class="flex items-center gap-1.5 px-6 pt-3 text-xs text-zinc-500 dark:text-zinc-400 md:hidden">
            <flux:icon.arrows-right-left class="size-3.5" /> Swipe the table to view other columns
        </div>
        <div class="overflow-x-auto px-5 pb-1">
            <flux:table class="w-full min-w-[900px]">
                <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-800/50">
                    <flux:table.column class="w-14 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">No.</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Program</flux:table.column>
                    <flux:table.column class="w-44 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Date</flux:table.column>
                    <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Format</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Venue</flux:table.column>
                    <flux:table.column class="w-32 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider" align="end">Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($programs as $program)
                        <flux:table.row :key="$program->id" class="transition-colors hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                            <flux:table.cell class="!py-4 font-medium tabular-nums text-zinc-400">
                                {{ ($programs->currentPage() - 1) * $programs->perPage() + $loop->iteration }}
                            </flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $program->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ str_replace('_', ' · ', $program->code->value) }}</p>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="!py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                @if ($program->starts_on)
                                    {{ $program->starts_on->format('d M Y') }}
                                    @if ($program->ends_on && !$program->ends_on->isSameDay($program->starts_on))
                                        <span class="block text-xs text-zinc-500">to {{ $program->ends_on->format('d M Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400">Not specified</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="!py-4">
                                @if ($program->delivery_mode)
                                    <flux:badge size="sm" :color="$program->delivery_mode === 'hybrid' ? 'emerald' : ($program->delivery_mode === 'onsite' ? 'blue' : 'zinc')">
                                        {{ ucfirst($program->delivery_mode) }}
                                    </flux:badge>
                                @else
                                    <span class="text-xs text-zinc-400">Not specified</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="!py-4"><span class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $program->venue ?: 'Not specified' }}</span></flux:table.cell>
                            <flux:table.cell class="!py-4" align="end">
                                <flux:button type="button" size="sm" variant="outline" icon="pencil-square" wire:click="edit({{ $program->id }})">Edit</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="!py-12 text-center">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">No programs found</p>
                                <p class="mt-1 text-sm text-zinc-500">Try another search, or check the master data seeder.</p>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        <div class="flex flex-col gap-3 border-t border-zinc-100 bg-zinc-50/65 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                <span class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-100">Page {{ $programs->currentPage() }} of {{ $programs->lastPage() }}</span>
                <span class="hidden text-zinc-300 sm:inline">|</span>
                <span class="tabular-nums text-zinc-500 dark:text-zinc-400">Showing {{ $programs->firstItem() ?? 0 }}–{{ $programs->lastItem() ?? 0 }} of {{ $programs->total() }}</span>
            </div>
            <div class="flex items-center justify-end gap-2">
                <flux:button size="sm" variant="outline" icon="chevron-left" wire:click="previousPage" :disabled="$programs->onFirstPage()">Previous</flux:button>
                <flux:button size="sm" variant="outline" icon:trailing="chevron-right" wire:click="nextPage" :disabled="! $programs->hasMorePages()">Next</flux:button>
            </div>
        </div>
    </section>

    <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
        <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
        <p><strong>Master data only.</strong> Editing a program does not register participants, change fees, activate package access, or publish website content. Those workflows are implemented in their separate approved phases.</p>
    </div>

    <flux:modal wire:model.self="showEditModal" class="md:w-[38rem]">
        @if ($editingProgramId !== null)
            <form wire:submit="save" class="space-y-5">
                <div class="space-y-1">
                    <flux:heading size="xl">Edit program details</flux:heading>
                    <flux:text size="sm">Update this program without changing its permanent reference code.</flux:text>
                </div>
                <div class="rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-800">
                    <p class="text-xs font-medium text-zinc-500">Permanent code</p>
                    <p class="mt-1 font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $editingCode }}</p>
                </div>
                <flux:input wire:model="name" label="Program name" required />
                <flux:textarea wire:model="description" label="Program description" rows="3" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input type="date" wire:model="startsOn" label="Start date" />
                    <flux:input type="date" wire:model="endsOn" label="End date" />
                </div>
                <flux:input wire:model="venue" label="Venue / location" placeholder="Leave blank when not confirmed" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="deliveryMode" label="Participation format">
                        <flux:select.option value="">Not specified</flux:select.option>
                        <flux:select.option value="hybrid">Hybrid</flux:select.option>
                        <flux:select.option value="onsite">Onsite</flux:select.option>
                        <flux:select.option value="online">Online</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="sortOrder" type="number" min="1" max="99" label="Display order" required />
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button type="button" variant="ghost" wire:click="cancel">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save">Save program</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
