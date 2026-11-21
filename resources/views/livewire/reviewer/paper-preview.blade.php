<div class="mx-auto w-full max-w-[1100px] space-y-6 pb-14">
    <section class="rounded-[1.5rem] bg-[#104b3c] px-6 py-8 text-white shadow-lg sm:px-9">
        <a href="{{ route('reviewer.workspace') }}" wire:navigate class="mb-5 inline-flex items-center gap-2 text-sm text-emerald-100 hover:text-white"><flux:icon.arrow-left class="size-4" /> Available Papers</a>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Paper preview</h1>
                <p class="mt-2 text-sm leading-6 text-emerald-50/85">Read-only academic material with Paper ID instead of author identity. Opening this paper does not claim it.</p>
            </div>
            <div class="shrink-0 rounded-2xl border border-white/15 bg-white/10 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-emerald-100/80">Paper ID</p>
                <p class="mt-1 font-mono text-lg font-semibold text-white">{{ $paper->reference }}</p>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="min-w-0 space-y-6">
            <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Paper details</h2>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-zinc-500">Title</p>
                <p class="mt-1 break-words text-base font-semibold leading-7">{{ $paper->title }}</p>
                <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-zinc-500">Conference track</p>
                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-200">{{ $paper->conferenceTrack?->name }}</p>
                <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-zinc-500">Submitted</p>
                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-200">{{ $paper->submitted_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
            </section>

            <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Abstract</h2>
                <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-zinc-700 dark:text-zinc-200">{{ filled($paper->abstract_text) ? trim($paper->abstract_text) : 'No abstract text saved.' }}</div>
                <h3 class="mt-6 text-sm font-semibold text-zinc-900 dark:text-white">Keywords</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($paper->keywords ?? [] as $keyword)
                        <flux:badge size="sm" color="zinc">{{ $keyword }}</flux:badge>
                    @endforeach
                </div>
                @if ($paper->abstract_pdf_path)
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/40">
                        <div class="flex min-w-0 items-center gap-3">
                            <flux:icon.document-text class="size-5 shrink-0 text-emerald-700" />
                            <p class="min-w-0 break-all text-sm font-medium text-zinc-800 dark:text-zinc-100">Abstract PDF</p>
                        </div>
                        <flux:button variant="outline" size="sm" icon="arrow-down-tray" :href="route('reviewer.papers.pdf', $paper->public_id)">Download PDF</flux:button>
                    </div>
                @endif
            </section>
        </div>
        <aside>
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Review workflow</h2>
                <ol class="mt-5 border-l-2 border-emerald-200 pl-4 dark:border-emerald-900" aria-label="Reviewer workflow timeline">
                    <li class="relative pb-6">
                        <span aria-hidden="true" class="absolute -left-[22px] top-1.5 size-3 rounded-full bg-emerald-500"></span>
                        <p class="text-sm font-semibold">Paper submitted</p>
                        <p class="mt-1 text-xs leading-5 text-zinc-500">{{ $paper->submitted_at ? $paper->submitted_at->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB' : 'Submission date unavailable' }}</p>
                    </li>
                    <li class="relative pb-6">
                        <span aria-hidden="true" class="absolute -left-[22px] top-1.5 size-3 rounded-full {{ $claimedByMe ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>
                        <p class="text-sm font-semibold">{{ $claimedByMe ? 'Claimed by you' : 'Claim paper' }}</p>
                        <p class="mt-1 text-xs leading-5 text-zinc-500">
                            {{ $claimedAt?->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? 'Not claimed yet' }}{{ $claimedAt ? ' WIB' : '' }}
                        </p>
                    </li>
                    <li class="relative pb-1">
                        <span aria-hidden="true" class="absolute -left-[22px] top-1.5 size-3 rounded-full border-2 border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900"></span>
                        <p class="text-sm font-semibold text-zinc-600 dark:text-zinc-300">Review submitted</p>
                        <p class="mt-1 text-xs leading-5 text-zinc-500">Not yet available · REV-003</p>
                    </li>
                </ol>
                <div class="mt-5 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    @if ($canClaim)
                        <flux:button type="button" variant="primary" icon="hand-raised" class="w-full" wire:click="askClaim">Claim this abstract</flux:button>
                    @else
                        <flux:badge color="emerald" size="sm">Assigned to you</flux:badge>
                    @endif
                    @error('claim')
                        <p role="alert" class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        </aside>
    </div>

    <flux:modal wire:model.self="claimModalOpen" class="md:w-[32rem]">
        @if ($claimModalOpen)
            <div class="space-y-5">
                <div class="flex items-start gap-3">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"><flux:icon.hand-raised class="size-5" /></div>
                    <div class="space-y-1">
                        <flux:heading size="xl">Claim this abstract?</flux:heading>
                        <flux:text size="sm">Paper ID: {{ $paper->reference }}</flux:text>
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
