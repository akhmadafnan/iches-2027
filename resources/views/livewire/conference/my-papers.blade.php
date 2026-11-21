<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-12 -top-28 size-80 rounded-full bg-emerald-400/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200">
                    <flux:icon.document-text class="size-4" /> ICHES 2027 / My Academic Workspace
                </p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">My Submissions</h1>
                <p class="mt-2 max-w-xl text-sm leading-7 text-emerald-50/85">Manage your drafts and submitted papers in one place. Only complete saved drafts can be submitted.</p>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.document-text class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500">My papers</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $totalPapers }}</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400"><flux:icon.pencil-square class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500">Saved drafts</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $draftCount }}</p></div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 bg-white px-5 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex size-12 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400"><flux:icon.check-circle class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500">Submitted papers</p><p class="mt-1 text-[26px] font-semibold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $submittedCount }}</p></div>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-6 py-6 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Paper directory</h2>
                <p class="mt-1 text-sm text-zinc-500">Your personal paper directory, saved drafts and submission status.</p>
            </div>
            @if ($totalPapers > 0 || trim($search) !== '')
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Find a title or paper ID" aria-label="Find my papers" class="w-full sm:w-72" />
            @endif
        </div>
        @if ($papers->isEmpty())
            <div class="flex min-h-[330px] flex-col items-center justify-center gap-4 px-6 py-12 text-center sm:min-h-[360px]">
                <div class="mb-1 flex size-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.document-text class="size-7" /></div>
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ trim($search) !== '' ? 'No matching papers' : 'No paper drafts found' }}</h3>
                    <p class="mx-auto max-w-[340px] text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ trim($search) !== '' ? 'Try another title or paper reference.' : 'Create your first paper draft to begin your submission.' }}</p>
                </div>
                @if ($totalPapers === 0)
                    <flux:button size="sm" variant="primary" icon="plus" class="mt-2" :href="route('papers.create')" wire:navigate>Start a draft</flux:button>
                @endif
            </div>
        @else
            <p class="flex items-center gap-1.5 px-6 pt-3 text-xs text-zinc-500 md:hidden"><flux:icon.arrows-right-left class="size-3.5" /> Swipe inside the table to view more columns</p>
        <div class="overflow-x-auto px-4 pb-1">
            <flux:table class="w-full min-w-[740px] lg:min-w-0">
                <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-800/50">
                    <flux:table.column class="w-14 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">No.</flux:table.column>
                    <flux:table.column class="w-32 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Paper ID</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Paper title / track</flux:table.column>
                    <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Status</flux:table.column>
                    <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Updated</flux:table.column>
                    <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider" align="end">Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($papers as $paper)
                        <flux:table.row :key="$paper->id" class="transition-colors hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                            <flux:table.cell class="!py-4 text-sm tabular-nums text-zinc-400">{{ ($papers->currentPage() - 1) * $papers->perPage() + $loop->iteration }}</flux:table.cell>
                            <flux:table.cell class="!py-4 font-mono text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $paper->reference }}</flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <p class="break-words text-sm font-semibold leading-6 text-zinc-900 dark:text-white">{{ $paper->title }}</p>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">{{ $paper->conferenceTrack?->name }}</p>
                            </flux:table.cell>
                            <flux:table.cell class="!py-4"><flux:badge :color="$paper->status->value === 'submitted' ? 'emerald' : 'amber'" size="sm">{{ ucfirst($paper->status->value) }}</flux:badge></flux:table.cell>
                            <flux:table.cell class="!py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $paper->updated_at?->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell class="!py-4" align="end">
                                <div class="flex items-center justify-end gap-1.5">
                                @if ($paper->status->value === 'draft')
                                    <flux:button size="sm" variant="outline" icon="pencil-square" :href="route('papers.edit', $paper->public_id)" wire:navigate tooltip="Edit draft" aria-label="Edit draft {{ $paper->reference }}" />
                                    @if (in_array($paper->public_id, $readyPaperIds, true))
                                        <flux:button size="sm" variant="outline" icon="paper-airplane" class="!text-emerald-700 dark:!text-emerald-400" wire:click="askSubmit(@js($paper->public_id))" tooltip="Submit paper" aria-label="Submit paper {{ $paper->reference }}" />
                                    @endif
                                @else
                                    <flux:button size="sm" variant="outline" icon="eye" :href="route('papers.show', $paper->public_id)" wire:navigate tooltip="View submission" aria-label="View submission {{ $paper->reference }}" />
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
                <span class="hidden text-zinc-300 sm:inline">|</span>
                <span class="tabular-nums text-zinc-500 dark:text-zinc-400">Showing {{ $papers->firstItem() ?? 0 }}–{{ $papers->lastItem() ?? 0 }} of {{ $papers->total() }}</span>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button size="sm" variant="outline" icon="chevron-left" wire:click="previousPage" :disabled="$papers->onFirstPage()">Previous</flux:button>
                <flux:button size="sm" variant="outline" icon:trailing="chevron-right" wire:click="nextPage" :disabled="! $papers->hasMorePages()">Next</flux:button>
            </div>
        </div>
        @endif
    </section>

    @error('paper')
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">{{ $message }}</div>
    @enderror

    <div class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
        <p><strong>One owned paper per account.</strong> Continue your existing Draft until you submit it. Submitted papers are read-only and eligible for academic review; co-authoring a paper owned by someone else is still allowed.</p>
    </div>

    <flux:modal wire:model.self="submitModalOpen" class="md:w-[32rem]">
        @if ($submitModalOpen)
            <div class="space-y-5">
                <div class="flex items-start gap-3">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"><flux:icon.paper-airplane class="size-5" /></div>
                    <div class="space-y-1">
                        <flux:heading size="xl">Submit paper?</flux:heading>
                        <flux:text size="sm">Paper {{ $confirmingReference }} is ready for academic submission.</flux:text>
                    </div>
                </div>
                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">After confirmation, the paper becomes <strong>Submitted</strong> and cannot be edited unless a formal revision is requested in a later phase. Its Paper ID remains unchanged.</p>
                @error('paper')
                    <p role="alert" class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button type="button" variant="ghost" wire:click="cancelSubmit">Cancel</flux:button>
                    <flux:button type="button" variant="primary" icon="check" wire:click="confirmSubmit" wire:loading.attr="disabled" wire:target="confirmSubmit">Confirm submission</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
