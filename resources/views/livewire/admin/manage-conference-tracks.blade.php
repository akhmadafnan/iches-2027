<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-32 size-96 rounded-full bg-emerald-400/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <p class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200">
                    <flux:icon.book-open class="size-4" /> ICHES 2027 / Academic Administration
                </p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">Conference Tracks</h1>
                <p class="mt-2 text-sm leading-7 text-emerald-50/85">
                    Manage the eight official research tracks from the ICHES 2027 TOR. Keep their permanent identifiers and original titles intact.
                </p>
            </div>
            <flux:button :href="route('admin.programs')" variant="primary" class="!bg-white !text-emerald-950 hover:!bg-emerald-50" icon:trailing="arrow-up-right" wire:navigate>
                View Programs
            </flux:button>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.book-open class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Official tracks</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $totalTracks }}</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400"><flux:icon.check-circle class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Available for selection</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $activeTracks }}</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400"><flux:icon.document-text class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Official source</p><p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">TOR · Section E.2</p></div>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-5 border-b border-zinc-100 px-6 py-6 dark:border-zinc-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Academic track directory</h2>
                    <flux:badge color="emerald" size="sm">{{ $tracks->total() }} results</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">The active tracks are the options that the later paper form will offer to authors.</p>
            </div>
            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search title or code" aria-label="Search tracks" class="w-full sm:w-[260px]" />
                <flux:select wire:model.live="statusFilter" aria-label="Filter track status" class="w-full sm:w-36">
                    <flux:select.option value="all">All statuses</flux:select.option>
                    <flux:select.option value="active">Active</flux:select.option>
                    <flux:select.option value="inactive">Inactive</flux:select.option>
                </flux:select>
            </div>
        </div>
        <div class="flex items-center gap-1.5 px-6 pt-3 text-xs text-zinc-500 md:hidden"><flux:icon.arrows-right-left class="size-3.5" /> Swipe the table to see all columns</div>
        <div class="overflow-x-auto px-5 pb-1">
            <flux:table class="w-full min-w-[850px]">
                <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-800/50">
                    <flux:table.column class="w-14 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">No.</flux:table.column>
                    <flux:table.column class="w-24 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Code</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Official academic track</flux:table.column>
                    <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Status</flux:table.column>
                    <flux:table.column class="w-24 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Order</flux:table.column>
                    <flux:table.column class="w-36 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider" align="end">Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($tracks as $track)
                        <flux:table.row :key="$track->id" class="transition-colors hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                            <flux:table.cell class="!py-4 text-sm font-medium tabular-nums text-zinc-400">{{ ($tracks->currentPage() - 1) * $tracks->perPage() + $loop->iteration }}</flux:table.cell>
                            <flux:table.cell class="!py-4"><span class="font-mono text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $track->code }}</span></flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <p class="text-sm font-semibold leading-6 text-zinc-900 dark:text-white">{{ $track->name }}</p>
                                @if ($track->name !== $track->official_title)
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">TOR title: {{ $track->official_title }}</p>
                                @elseif ($track->description)
                                    <p class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $track->description }}</p>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <flux:badge :color="$track->is_active ? 'emerald' : 'zinc'" size="sm">{{ $track->is_active ? 'Active' : 'Inactive' }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="!py-4 font-medium tabular-nums text-zinc-600 dark:text-zinc-300">{{ $track->sort_order }}</flux:table.cell>
                            <flux:table.cell class="!py-4" align="end">
                                <flux:button type="button" size="sm" variant="outline" icon="pencil-square" wire:click="edit({{ $track->id }})">Edit track</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row><flux:table.cell colspan="6" class="!py-14 text-center">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">No matching tracks</p>
                            <p class="mt-1 text-sm text-zinc-500">Try another search or status filter.</p>
                        </flux:table.cell></flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        <div class="flex flex-col gap-3 border-t border-zinc-100 bg-zinc-50/65 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                <span class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-100">Page {{ $tracks->currentPage() }} of {{ $tracks->lastPage() }}</span>
                <span class="hidden text-zinc-300 sm:inline">|</span>
                <span class="tabular-nums text-zinc-500 dark:text-zinc-400">Showing {{ $tracks->firstItem() ?? 0 }}–{{ $tracks->lastItem() ?? 0 }} of {{ $tracks->total() }}</span>
            </div>
            <div class="flex items-center justify-end gap-2">
                <flux:button size="sm" variant="outline" icon="chevron-left" wire:click="previousPage" :disabled="$tracks->onFirstPage()">Previous</flux:button>
                <flux:button size="sm" variant="outline" icon:trailing="chevron-right" wire:click="nextPage" :disabled="! $tracks->hasMorePages()">Next</flux:button>
            </div>
        </div>
    </section>

    <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
        <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
        <p><strong>Academic master data only.</strong> Track selection is prepared for the upcoming abstract submission form. This page does not create papers, assign reviewers, or open submissions.</p>
    </div>

    <flux:modal wire:model.self="showEditModal" class="md:w-[37rem]">
        @if ($editingTrackId !== null)
            <form wire:submit="save" class="space-y-5">
                <div class="space-y-1">
                    <flux:heading size="xl">Edit conference track</flux:heading>
                    <flux:text size="sm">Change display details without altering the official TOR classification.</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Permanent identifier · {{ $editingCode }}</p>
                    <p class="mt-1 text-sm font-semibold leading-6 text-zinc-800 dark:text-zinc-200">{{ $officialTitle }}</p>
                </div>
                <flux:input wire:model="name" label="Track display name" required />
                <flux:textarea wire:model="description" label="Description (optional)" rows="3" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="sortOrder" type="number" min="1" max="99" label="Display order" required />
                    <div class="flex flex-col justify-end gap-2 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:checkbox wire:model="isActive" label="Available for selection" />
                        <p class="text-xs leading-5 text-zinc-500 dark:text-zinc-400">Inactive tracks will be hidden from future author choices.</p>
                    </div>
                </div>
                @error('isActive') <flux:text size="sm" class="text-red-600">{{ $message }}</flux:text> @enderror
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button type="button" variant="ghost" wire:click="cancel">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save">Save track</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
