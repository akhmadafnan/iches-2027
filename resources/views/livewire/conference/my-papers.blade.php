<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-12 -top-28 size-80 rounded-full bg-emerald-400/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200">
                    <flux:icon.document-text class="size-4" /> ICHES 2027 / My Academic Workspace
                </p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">My Submissions</h1>
                <p class="mt-2 max-w-xl text-sm leading-7 text-emerald-50/85">Start and manage your conference paper drafts. Each draft stays private until you submit it in a later step.</p>
            </div>
            <flux:button :href="route('papers.create')" variant="primary" class="!bg-white !text-emerald-950 hover:!bg-emerald-50" icon="plus" wire:navigate>
                New paper draft
            </flux:button>
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
            <div class="flex size-12 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400"><flux:icon.shield-check class="size-6" /></div>
            <div><p class="text-xs font-medium text-zinc-500">Visibility</p><p class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">My papers only</p></div>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-[0_8px_30px_-18px_rgba(15,23,42,.14)] dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-6 py-6 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Paper directory</h2>
                <p class="mt-1 text-sm text-zinc-500">Your personal list of paper drafts and reference numbers.</p>
            </div>
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Find a title or paper ID" aria-label="Find my papers" class="w-full sm:w-72" />
        </div>
        @if ($papers->isEmpty())
            <div class="flex flex-col items-center gap-3 px-6 py-12 text-center">
                <div class="mb-1 flex size-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.document-text class="size-7" /></div>
                <p class="text-base font-semibold text-zinc-900 dark:text-white">{{ trim($search) !== '' ? 'No matching papers' : 'No paper drafts found' }}</p>
                <p class="max-w-sm text-sm leading-6 text-zinc-500">{{ trim($search) !== '' ? 'Try a different title or paper ID.' : 'Create your first draft to register a paper title, research track, and author list.' }}</p>
                <flux:button size="sm" variant="primary" icon="plus" class="mt-2" :href="route('papers.create')" wire:navigate>Start a draft</flux:button>
            </div>
        @else
            <p class="flex items-center gap-1.5 px-6 pt-3 text-xs text-zinc-500 md:hidden"><flux:icon.arrows-right-left class="size-3.5" /> Swipe inside the table to view more columns</p>
        <div class="overflow-x-auto px-5 pb-1">
            <flux:table class="w-full min-w-[870px]">
                <flux:table.columns class="bg-zinc-50/80 dark:bg-zinc-800/50">
                    <flux:table.column class="w-14 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">No.</flux:table.column>
                    <flux:table.column class="w-40 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Paper ID</flux:table.column>
                    <flux:table.column class="!py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Paper title / track</flux:table.column>
                    <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Status</flux:table.column>
                    <flux:table.column class="w-36 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider">Updated</flux:table.column>
                    <flux:table.column class="w-28 !py-4 !text-[11px] !font-semibold uppercase !tracking-wider" align="end">Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($papers as $paper)
                        <flux:table.row :key="$paper->id" class="transition-colors hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                            <flux:table.cell class="!py-4 text-sm tabular-nums text-zinc-400">{{ ($papers->currentPage() - 1) * $papers->perPage() + $loop->iteration }}</flux:table.cell>
                            <flux:table.cell class="!py-4 font-mono text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $paper->reference }}</flux:table.cell>
                            <flux:table.cell class="!py-4">
                                <p class="text-sm font-semibold leading-6 text-zinc-900 dark:text-white">{{ $paper->title }}</p>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">{{ $paper->conferenceTrack?->name }}</p>
                            </flux:table.cell>
                            <flux:table.cell class="!py-4"><flux:badge color="amber" size="sm">{{ ucfirst($paper->status->value) }}</flux:badge></flux:table.cell>
                            <flux:table.cell class="!py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $paper->updated_at?->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell class="!py-4" align="end">
                                @if ($paper->status === \App\Enums\PaperStatus::Draft)
                                    <flux:button size="sm" variant="outline" icon="pencil-square" :href="route('papers.edit', $paper->public_id)" wire:navigate>Edit draft</flux:button>
                                @else
                                    <flux:badge size="sm" color="zinc">Read-only</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
        @endif
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
    </section>

    <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
        <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
        <p><strong>Draft ≠ Submitted.</strong> This phase records only titles, track choices, and author metadata. Abstract text, keywords, private PDF upload, and the Submit action arrive in the next approved phases. Reviewers cannot claim these drafts.</p>
    </div>
</div>
