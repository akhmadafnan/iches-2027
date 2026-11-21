<div class="mx-auto w-full max-w-[1100px] space-y-6 pb-14">
    <section class="rounded-[1.5rem] bg-[#104b3c] px-6 py-8 text-white shadow-lg sm:px-9">
        <a href="{{ route('reviewer.workspace') }}" wire:navigate class="mb-5 inline-flex items-center gap-2 text-sm text-emerald-100 hover:text-white"><flux:icon.arrow-left class="size-4" /> Available Papers</a>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Paper preview</h1>
                <p class="mt-2 text-sm leading-6 text-emerald-50/85">Read-only academic material. Opening this paper does not claim it.</p>
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
                <div class="mt-4 space-y-3">
                    <div class="flex items-start gap-3"><div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950"><flux:icon.eye class="size-4" /></div><div><p class="text-sm font-semibold">Preview available</p><p class="mt-1 text-xs text-zinc-500">You can read the abstract and PDF.</p></div></div>
                    <div class="flex items-start gap-3"><div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800"><flux:icon.hand-raised class="size-4" /></div><div><p class="text-sm font-semibold">Claim paper</p><p class="mt-1 text-xs text-zinc-500">Coming in REV-002; no assignment created yet.</p></div></div>
                    <div class="flex items-start gap-3"><div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800"><flux:icon.clipboard-document-check class="size-4" /></div><div><p class="text-sm font-semibold">Submit review</p><p class="mt-1 text-xs text-zinc-500">Coming in REV-003 after a valid claim.</p></div></div>
                </div>
            </section>
        </aside>
    </div>
</div>
