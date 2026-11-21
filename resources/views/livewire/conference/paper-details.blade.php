<div class="mx-auto w-full max-w-[1100px] space-y-6 pb-14">
    <section class="rounded-[1.5rem] bg-[#104b3c] px-6 py-8 text-white shadow-lg sm:px-9">
        <a href="{{ route('papers.index') }}" wire:navigate
            class="mb-5 inline-flex items-center gap-2 text-sm text-emerald-100 hover:text-white">
            <flux:icon.arrow-left class="size-4" />
            My Submissions
        </a>

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Paper submission</h1>
                <p class="mt-2 text-sm leading-6 text-emerald-50/85">
                    Read-only record of your academic submission and status history.
                </p>
            </div>

            <div class="flex shrink-0 flex-col gap-3 lg:items-end">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-100/80">
                        Paper ID
                    </p>
                    <p class="mt-1 font-mono text-lg font-semibold tracking-wide text-white">
                        {{ $paper->reference }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_19rem]">
        <div class="space-y-6 min-w-0">
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">Paper details</h2>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-zinc-500">Title</p>
                <p class="mt-1 break-words text-base font-semibold leading-7">{{ $paper->title }}</p>
                <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-zinc-500">Conference track</p>
                <p class="mt-1 text-sm">{{ $paper->conferenceTrack?->name }}</p>

                <h3 class="mt-7 border-t border-zinc-200 pt-5 text-sm font-semibold dark:border-zinc-800">Authors</h3>
                <div class="mt-3 space-y-3">
                    @foreach ($paper->authors as $author)
                        <div class="rounded-xl bg-zinc-50 px-4 py-3 dark:bg-zinc-800/50">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold">{{ $loop->iteration }}. {{ $author->name }}</span>
                                @if ($author->is_corresponding)
                                    <flux:badge size="sm" color="emerald">Corresponding</flux:badge>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $author->institution }}{{ $author->country ? ' · ' . $author->country : '' }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            @if ($paper->decision)
                <section class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/25">
                    <h2 class="text-lg font-semibold">Published academic decision</h2>
                    <flux:badge class="mt-3" :color="$paper->decision->outcome === 'accepted' ? 'emerald' : 'rose'" size="sm">{{ ucfirst($paper->decision->outcome) }}</flux:badge>
                    <p class="mt-3 text-xs text-zinc-600 dark:text-zinc-300">Published {{ $paper->decision->published_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB · Decision version {{ $paper->decision->version }}</p>
                    @if (filled($paper->decision->public_comments))
                        <h3 class="mt-5 text-sm font-semibold">Reviewer General Comments</h3>
                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-7 text-zinc-700 dark:text-zinc-200">{{ $paper->decision->public_comments }}</p>
                    @else
                        <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">No General Comments were provided with the finalized review.</p>
                    @endif
                    @if ($paper->decision->outcome === 'accepted')
                        <p class="mt-4 text-xs leading-6 text-zinc-600 dark:text-zinc-300">Acceptance is published. Official LoA generation, payment and Full Paper upload are separate workflows that are not yet available.</p>
                    @else
                        <p class="mt-4 text-xs leading-6 text-zinc-600 dark:text-zinc-300">This submission is not eligible for LoA, payment or Full Paper unless an authorized correction changes the published decision.</p>
                    @endif
                </section>
            @endif

            <section
                class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">Abstract</h2>
                <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-zinc-700 dark:text-zinc-200">{{ filled($paper->abstract_text) ? trim($paper->abstract_text) : 'No abstract text saved.' }}</div>
                <h3 class="mt-6 text-sm font-semibold">Keywords</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse ($paper->keywords ?? [] as $keyword)
                        <flux:badge color="zinc">{{ $keyword }}</flux:badge>
                    @empty
                        <span class="text-sm text-zinc-500">No keywords saved.</span>
                    @endforelse
                </div>
                @if ($paper->abstract_pdf_path)
                    <div
                        class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/40">
                        <div class="flex min-w-0 items-center gap-3">
                            <flux:icon.document-text class="size-5 shrink-0 text-emerald-700" />
                            <span class="break-all text-sm font-medium">{{ $paper->abstract_pdf_original_name }}</span>
                        </div>
                        <flux:button size="sm" variant="outline" icon="arrow-down-tray"
                            :href="route('papers.abstract.download', $paper->public_id)">Download PDF</flux:button>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-5">
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-base font-semibold">Status timeline</h2>
                <ol class="mt-5 border-l-2 border-emerald-200 pl-4 dark:border-emerald-900" aria-label="Paper activity history">
                    @foreach ($activity as $event)
                        <li class="relative pb-5 last:pb-1">
                            <span aria-hidden="true" class="absolute -left-[22px] top-1.5 size-3 rounded-full bg-emerald-500"></span>
                            <p class="text-sm font-semibold">{{ $event['label'] }}</p>
                            <p class="mt-1 text-xs leading-5 text-zinc-500">
                                @if ($event['detail'])
                                    {{ $event['detail'] }} ·
                                @endif
                                {{ $event['occurred_at']->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                            </p>
                        </li>
                    @endforeach
                </ol>
                @if ($paper->status->value === 'draft')
                    <flux:button size="sm" variant="outline" icon="pencil-square"
                        :href="route('papers.edit', $paper->public_id)" wire:navigate class="w-full">Edit draft
                    </flux:button>
                @else
                    <p class="mt-3 text-xs leading-5 text-zinc-500">Editing is locked. Academic milestones appear here when recorded; reviewer identities and internal decisions remain private.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
