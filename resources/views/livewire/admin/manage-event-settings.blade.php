<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-32 size-96 rounded-full bg-emerald-400/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200"><flux:icon.adjustments-horizontal class="size-4" /> ICHES 2027 / Administration</p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">Event Settings</h1>
                <p class="mt-2 max-w-2xl text-sm leading-7 text-emerald-50/85">Control the official conference identity, calendar, venue and important dates from the source TOR.</p>
            </div>
            <flux:button :href="route('admin.programs')" variant="primary" class="!bg-white !text-emerald-950 hover:!bg-emerald-50" icon:trailing="arrow-up-right" wire:navigate>View Programs</flux:button>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.75fr)_minmax(270px,.85fr)]">
        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-6 py-5 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Conference identity &amp; schedule</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Details are editable, with confirmed TOR values filled initially.</p>
            </div>
            <form wire:submit="save" class="space-y-5 px-6 py-6">
                <div class="grid gap-4 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <flux:input wire:model="name" label="Full event name" required />
                    <flux:input wire:model="shortName" label="Short name" required />
                </div>
                <flux:textarea wire:model="theme" label="Official conference theme" rows="3" />
                <flux:input wire:model="organizer" label="Organizer / host" />
                <flux:input wire:model="mainVenue" label="Main conference venue" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="startsOn" type="date" label="Event start date" required />
                    <flux:input wire:model="endsOn" type="date" label="Event end date" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <flux:input wire:model="startTime" type="time" label="Event start time (optional)" />
                        <p class="text-xs leading-5 text-zinc-500 dark:text-zinc-400">Leave blank until an official countdown start time is confirmed.</p>
                    </div>
                    <flux:select wire:model="timezone" label="Event timezone" required>
                        <flux:select.option value="Asia/Jakarta">Asia/Jakarta (WIB)</flux:select.option>
                        <flux:select.option value="Asia/Makassar">Asia/Makassar (WITA)</flux:select.option>
                        <flux:select.option value="Asia/Jayapura">Asia/Jayapura (WIT)</flux:select.option>
                        <flux:select.option value="UTC">UTC</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                    <p class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400"><flux:icon.shield-check class="size-4 text-emerald-600" /> Admin-only configuration</p>
                    <flux:button variant="primary" type="submit" icon="check" wire:loading.attr="disabled" wire:target="save">Save event settings</flux:button>
                </div>
            </form>
        </section>
        <aside class="space-y-4">
            <div class="rounded-2xl border border-zinc-200/90 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.document-text class="size-6" /></div>
                <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-white">Official source</h3>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">TOR ICHES 6 UNISYA 2027, sections E–H and L. Conference: 5 January 2027. Community service and destination study: 6 January 2027.</p>
                <flux:badge color="emerald" size="sm" class="mt-4">Source-aligned master data</flux:badge>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900 dark:bg-amber-950/30">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-amber-900 dark:text-amber-200"><flux:icon.information-circle class="size-5" /> Important distinction</h3>
                <p class="mt-3 text-sm leading-6 text-amber-900/85 dark:text-amber-200/90">These calendar dates are planning targets. They do not automatically unlock Full Paper, publish academic decisions or verify payments.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200/90 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Countdown readiness</h3>
                <flux:badge :color="$hasExactStartTime ? 'emerald' : 'amber'" size="sm" class="mt-3">{{ $hasExactStartTime ? 'Start time confirmed' : 'Start time pending' }}</flux:badge>
                <p class="mt-3 text-xs leading-5 text-zinc-500 dark:text-zinc-400">The public website remains on hold. Only a saved official start time may power the future countdown.</p>
            </div>
        </aside>
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 px-6 py-6 dark:border-zinc-800">
            <div>
                <div class="flex items-center gap-2"><h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Important dates</h2><flux:badge color="emerald" size="sm">{{ $milestones->total() }} milestones</flux:badge></div>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Admin-editable dates from the official TOR.</p>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-zinc-500"><flux:icon.calendar-days class="size-4" /> TOR calendar</div>
        </div>
        <p class="flex items-center gap-1.5 px-6 pt-3 text-xs text-zinc-500 md:hidden"><flux:icon.arrows-right-left class="size-3.5" /> Swipe horizontally for all columns</p>
        <div class="overflow-x-auto px-5 pb-1">
            <flux:table class="w-full min-w-[800px]">
                <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-800/50">
                    <flux:table.column class="w-14 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">No.</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Milestone</flux:table.column>
                    <flux:table.column class="w-48 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Target date</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Admin note</flux:table.column>
                    <flux:table.column align="end" class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($milestones as $milestone)
                        <flux:table.row :key="$milestone->id" class="transition-colors hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                            <flux:table.cell class="!py-4 text-sm tabular-nums text-zinc-400">{{ ($milestones->currentPage() - 1) * $milestones->perPage() + $loop->iteration }}</flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $milestone->name }}</p>
                                <p class="mt-1 font-mono text-xs text-zinc-500">{{ $milestone->code }}</p>
                            </flux:table.cell>
                            <flux:table.cell class="!py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $milestone->starts_on?->format('d M Y') ?? 'Not specified' }}
                                @if ($milestone->ends_on && ! $milestone->ends_on->isSameDay($milestone->starts_on))
                                    <span class="block text-xs text-zinc-500">to {{ $milestone->ends_on->format('d M Y') }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="!py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $milestone->notes ?: '—' }}</flux:table.cell>
                            <flux:table.cell align="end" class="!py-4"><flux:button size="sm" variant="outline" icon="pencil-square" wire:click="editMilestone({{ $milestone->id }})">Edit</flux:button></flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row><flux:table.cell colspan="5" class="!py-12 text-center"><p class="text-sm text-zinc-500">No milestones configured. Seed official dates first.</p></flux:table.cell></flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        <div class="flex flex-col gap-3 border-t border-zinc-100 bg-zinc-50/65 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-100">Page {{ $milestones->currentPage() }} of {{ $milestones->lastPage() }}</span>
                <span class="tabular-nums text-zinc-500 dark:text-zinc-400">Showing {{ $milestones->firstItem() ?? 0 }}–{{ $milestones->lastItem() ?? 0 }} of {{ $milestones->total() }}</span>
            </div>
            <div class="flex items-center justify-end gap-2">
                <flux:button size="sm" variant="outline" icon="chevron-left" wire:click="previousPage" :disabled="$milestones->onFirstPage()">Previous</flux:button>
                <flux:button size="sm" variant="outline" icon:trailing="chevron-right" wire:click="nextPage" :disabled="! $milestones->hasMorePages()">Next</flux:button>
            </div>
        </div>
    </section>

    <flux:modal wire:model.self="showMilestoneModal" class="md:w-[34rem]">
        @if ($editingMilestoneId !== null)
            <form wire:submit="saveMilestone" class="space-y-5">
                <div class="space-y-1">
                    <flux:heading size="xl">Edit important date</flux:heading>
                    <flux:text size="sm">Update a planning milestone; this cannot change academic or payment entitlements.</flux:text>
                </div>
                <flux:input wire:model="milestoneName" label="Milestone name" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="milestoneStartsOn" type="date" label="Start date" />
                    <flux:input wire:model="milestoneEndsOn" type="date" label="End date" />
                </div>
                <flux:textarea wire:model="milestoneNotes" label="Admin note (optional)" rows="2" />
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button type="button" variant="ghost" wire:click="cancelMilestone">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="saveMilestone">Save date</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
