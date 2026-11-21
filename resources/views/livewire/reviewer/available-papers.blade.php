<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    <section class="relative overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-12 -top-28 size-80 rounded-full bg-emerald-400/10 blur-2xl"></div>
        <div class="relative">
            <p class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200"><flux:icon.academic-cap class="size-4" /> ICHES 2027 / Reviewer Workspace</p>
            <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">Reviewer Workspace</h1>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-emerald-50/85">Browse submitted abstracts, claim one for review, and manage your assigned papers. Each abstract can have only one active reviewer.</p>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.document-text class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500">Available Papers</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $totalAvailable }}</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-400"><flux:icon.book-open class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500">Tracks represented</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $activeTracksInPool }}</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"><flux:icon.clipboard-document-check class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500">My Reviews</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $totalAssigned }}</p></div>
        </div>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
    @endif
    <div class="flex w-full items-center gap-1 rounded-xl border border-zinc-200 bg-white p-1 sm:w-fit dark:border-zinc-800 dark:bg-zinc-900" role="group" aria-label="Reviewer paper lists">
        <button type="button" wire:click="selectTab('available')" aria-pressed="{{ $tab === 'available' ? 'true' : 'false' }}" class="flex-1 rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors sm:flex-none {{ $tab === 'available' ? 'bg-emerald-700 text-white' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">Available Papers <span class="ml-1 text-xs opacity-80">{{ $totalAvailable }}</span></button>
        <button type="button" wire:click="selectTab('mine')" aria-pressed="{{ $tab === 'mine' ? 'true' : 'false' }}" class="flex-1 rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors sm:flex-none {{ $tab === 'mine' ? 'bg-emerald-700 text-white' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">My Reviews <span class="ml-1 text-xs opacity-80">{{ $totalAssigned }}</span></button>
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-6 py-5 dark:border-zinc-800">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $tab === 'mine' ? 'My Reviews' : 'Available Papers' }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ $tab === 'mine' ? 'Manage your assigned abstracts and review progress here.' : 'Submitted abstracts not yet claimed by a reviewer. Only Paper ID and academic content are shown.' }}</p>
                </div>
                <flux:badge color="emerald" size="sm">{{ $tab === 'mine' ? 'My assigned papers' : '1 reviewer per abstract' }}</flux:badge>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" label="Search papers" placeholder="Search title or Paper ID" aria-label="Search available papers" />
                </div>
                <div class="min-w-0 sm:w-80">
                    <flux:select wire:model.live="trackFilter" label="Conference track" aria-label="Filter by conference track">
                        <flux:select.option value="">All conference tracks</flux:select.option>
                        @foreach ($tracks as $track)
                            <flux:select.option :value="(string) $track->id">{{ $track->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                @if ($search !== '' || $trackFilter !== '')
                    <flux:button type="button" variant="ghost" icon="x-mark" wire:click="clearFilters">Clear</flux:button>
                @endif
            </div>
        </div>

        @if ($papers->isEmpty())
            <div class="flex min-h-[300px] flex-col items-center justify-center gap-3 px-6 py-12 text-center">
                <div class="flex size-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.document-magnifying-glass class="size-7" /></div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $search !== '' || $trackFilter !== '' ? 'No matching papers' : ($tab === 'mine' ? 'No claimed papers yet' : 'No papers available yet') }}</h3>
                <p class="max-w-sm text-sm leading-6 text-zinc-500">{{ $search !== '' || $trackFilter !== '' ? 'Try a different search or conference track.' : ($tab === 'mine' ? 'Claim an available abstract to see it listed here.' : 'Unclaimed submitted abstracts will appear automatically. Drafts and abstracts already claimed by others are not listed.') }}</p>
                @if ($search !== '' || $trackFilter !== '')
                    <flux:button type="button" variant="outline" size="sm" wire:click="clearFilters">Clear filters</flux:button>
                @endif
            </div>
        @else
            <p class="flex items-center gap-1.5 px-5 pt-3 text-xs text-zinc-500 md:hidden"><flux:icon.arrows-right-left class="size-3.5" /> Swipe inside the table to view more columns</p>
            <div class="overflow-x-auto px-4 pb-1">
                <flux:table class="w-full min-w-[750px] lg:min-w-0">
                    <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-800/50">
                        <flux:table.column class="w-12 !py-4 !text-[11px] !font-semibold uppercase">No.</flux:table.column>
                        <flux:table.column class="w-36 !py-4 !text-[11px] !font-semibold uppercase">Paper ID</flux:table.column>
                        <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase">Paper title / track</flux:table.column>
                        <flux:table.column class="w-32 !py-4 !text-[11px] !font-semibold uppercase">Submitted</flux:table.column>
                        <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase" align="end">Action</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($papers as $paper)
                            <flux:table.row :key="$paper->id" class="transition-colors hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                                <flux:table.cell class="!py-4 text-sm tabular-nums text-zinc-400">{{ ($papers->currentPage() - 1) * $papers->perPage() + $loop->iteration }}</flux:table.cell>
                                <flux:table.cell class="!py-4 font-mono text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $paper->reference }}</flux:table.cell>
                                <flux:table.cell class="!py-4">
                                    <p class="break-words text-sm font-semibold leading-6 text-zinc-900 dark:text-white">{{ $paper->title }}</p>
                                    <p class="mt-1 text-xs leading-5 text-zinc-500">{{ $paper->conferenceTrack?->name }}</p>
                                    @if ($tab === 'mine')
                                        @php($currentReview = $paper->reviewClaims->first()?->review)
                                        <flux:badge size="sm" class="mt-2" :color="$currentReview?->status?->value === 'submitted' ? 'emerald' : ($currentReview ? 'amber' : 'zinc')">
                                            {{ $currentReview?->status?->value === 'submitted' ? 'Review Submitted' : ($currentReview ? 'Review Draft' : 'Claimed') }}
                                        </flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="!py-4 text-xs tabular-nums text-zinc-600 dark:text-zinc-300">{{ $paper->submitted_at?->timezone('Asia/Jakarta')->format('d M Y') }}</flux:table.cell>
                                <flux:table.cell class="!py-4" align="end">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($tab === 'mine')
                                            <flux:button size="sm" variant="outline" :icon="$currentReview?->status?->value === 'submitted' ? 'eye' : 'pencil-square'" :href="route('reviewer.papers.show', $paper->public_id)" wire:navigate :tooltip="$currentReview?->status?->value === 'submitted' ? 'View submitted review' : 'Open review form'" :aria-label="($currentReview?->status?->value === 'submitted' ? 'View submitted review ' : 'Open review form ').$paper->reference" />
                                        @else
                                            <flux:button size="sm" variant="outline" icon="eye" :href="route('reviewer.papers.show', $paper->public_id)" wire:navigate tooltip="Preview paper" aria-label="Preview paper {{ $paper->reference }}" />
                                        @endif
                                        @if ($tab === 'available')
                                            <flux:button size="sm" variant="outline" icon="hand-raised" class="!text-emerald-700 dark:!text-emerald-400" wire:click="askClaim(@js($paper->public_id))" tooltip="Claim paper" aria-label="Claim paper {{ $paper->reference }}" />
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @endif
        @if ($papers->total() > 0)
            <div class="flex flex-col gap-3 border-t border-zinc-100 bg-zinc-50/65 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                    <span class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-100">Page {{ $papers->currentPage() }} of {{ $papers->lastPage() }}</span>
                    <span class="text-zinc-500">Showing {{ $papers->firstItem() ?? 0 }}–{{ $papers->lastItem() ?? 0 }} of {{ $papers->total() }}</span>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:button size="sm" variant="outline" icon="chevron-left" wire:click="previousPage" :disabled="$papers->onFirstPage()">Previous</flux:button>
                    <flux:button size="sm" variant="outline" icon:trailing="chevron-right" wire:click="nextPage" :disabled="! $papers->hasMorePages()">Next</flux:button>
                </div>
            </div>
        @endif
    </section>

    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm leading-6 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        <p><strong>Reviewer workflow:</strong> Claim an available abstract, open it from My Reviews, then write General Comments and choose Accept or Reject. Save Draft or confirm Submit Review. Recommendations remain internal until the academic decision is formally published. Author identities and affiliations are not displayed in this workspace.</p>
    </div>

    <flux:modal wire:model.self="claimModalOpen" class="md:w-[32rem]">
        @if ($claimModalOpen)
            <div class="space-y-5">
                <div class="flex items-start gap-3">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"><flux:icon.hand-raised class="size-5" /></div>
                    <div class="space-y-1">
                        <flux:heading size="xl">Claim this abstract?</flux:heading>
                        <flux:text size="sm">Paper ID: {{ $confirmingReference }}</flux:text>
                    </div>
                </div>
                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">You will become the only active reviewer assigned to this abstract. It will move to My Reviews. Claiming does not submit a review.</p>
                @error('claim')
                    <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button type="button" variant="ghost" wire:click="cancelClaim">Cancel</flux:button>
                    <flux:button type="button" variant="primary" icon="check" wire:click="confirmClaim" wire:loading.attr="disabled" wire:target="confirmClaim">Confirm claim</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
