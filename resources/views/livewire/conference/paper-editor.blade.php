<div class="mx-auto w-full max-w-[1180px] space-y-6 pb-14">
    <section class="relative isolate overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-7 py-8 text-white shadow-[0_12px_32px_-18px_rgba(16,75,60,.55)] sm:px-9 sm:py-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-12 -top-28 size-80 rounded-full bg-emerald-400/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.18em] text-emerald-200">
                    <flux:icon.pencil-square class="size-4" /> ICHES 2027 / Conference Submission
                </p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-[34px]">{{ $editingPublicId ? 'Edit Paper Draft' : 'New Paper Draft' }}</h1>
                <p class="mt-2 max-w-xl text-sm leading-7 text-emerald-50/85">Record the paper title, research track and author list. Saving a draft does not submit it for peer review.</p>
            </div>
            <flux:button :href="route('papers.index')" variant="primary" class="!bg-white !text-emerald-950 hover:!bg-emerald-50" icon="arrow-left" wire:navigate>
                My Submissions
            </flux:button>
        </div>
    </section>

    @if ($editingPublicId)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 dark:border-emerald-900 dark:bg-emerald-950/30">
            <div><p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">Permanent paper ID</p><p class="mt-1 font-mono text-base font-bold tracking-wide text-emerald-950 dark:text-white">{{ $paperReference }}</p></div>
            <flux:badge color="amber" size="sm">Draft / not submitted</flux:badge>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-6 py-5 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"><flux:icon.book-open class="size-5" /></div>
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Paper details</h2>
                        <p class="mt-1 text-sm text-zinc-500">The academic track is selected from the eight TOR-approved themes.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-5 p-6">
                <flux:textarea wire:model="title" label="Paper title" placeholder="Enter your working academic paper title" rows="2" required />
                <flux:select wire:model="conferenceTrackId" label="Conference track" required>
                    <flux:select.option value="">Select one of the official tracks</flux:select.option>
                    @foreach ($tracks as $track)
                        <flux:select.option :value="$track->id">{{ $track->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('conferenceTrackId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="flex items-center gap-2 text-xs leading-5 text-zinc-500"><flux:icon.information-circle class="size-4 shrink-0" /> Only active academic tracks can be selected for a new draft.</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-100 px-6 py-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400"><flux:icon.users class="size-5" /></div>
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Authors &amp; affiliations</h2>
                        <p class="mt-1 text-sm text-zinc-500">Arrange authors in publication order and select one contact author.</p>
                    </div>
                </div>
                <flux:button type="button" size="sm" variant="outline" icon="plus" wire:click="addAuthor">Add co-author</flux:button>
            </div>

            <div class="space-y-4 p-5 sm:p-6">
                @foreach ($authors as $index => $author)
                    <div wire:key="author-{{ $author['key'] }}" class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-700 dark:bg-zinc-800/40 sm:p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">{{ $index + 1 }}</div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Author {{ $index + 1 }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-up" wire:click="moveAuthor({{ $index }}, 'up')" :disabled="$index === 0" tooltip="Move author up" />
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-down" wire:click="moveAuthor({{ $index }}, 'down')" :disabled="$index === count($authors) - 1" tooltip="Move author down" />
                                <flux:button type="button" size="sm" variant="ghost" icon="trash" wire:click="removeAuthor({{ $index }})" :disabled="count($authors) === 1">Remove</flux:button>
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:input wire:model="authors.{{ $index }}.name" label="Full name" placeholder="Author name" required />
                            <flux:input wire:model="authors.{{ $index }}.email" type="email" label="Email address" placeholder="author@example.com" required />
                            <flux:input wire:model="authors.{{ $index }}.institution" label="Institution / affiliation" placeholder="University or organization" />
                            <flux:input wire:model="authors.{{ $index }}.country" label="Country" placeholder="Country" />
                        </div>
                    </div>
                @endforeach

                @error('authors') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="grid gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900 dark:bg-emerald-950/20 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] sm:items-center">
                    <div>
                        <p class="text-sm font-semibold text-emerald-950 dark:text-emerald-100">Corresponding author</p>
                        <p class="mt-1 text-xs leading-5 text-emerald-800/80 dark:text-emerald-300">This author is the primary academic contact. Only your account can manage this draft.</p>
                    </div>
                    <flux:select wire:model="correspondingIndex" aria-label="Corresponding author">
                        @foreach ($authors as $index => $author)
                            <flux:select.option :value="$index">Author {{ $index + 1 }}{{ filled($author['name']) ? ' — '.$author['name'] : '' }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                @error('correspondingIndex') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <div class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-zinc-200 bg-white px-6 py-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center">
            <div class="flex items-start gap-3">
                <flux:icon.shield-check class="mt-0.5 size-5 shrink-0 text-emerald-700" />
                <div>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Save privately as Draft</p>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">No reviewer access, no submission email, and no payment is triggered at this stage.</p>
                </div>
            </div>
            <div class="flex w-full flex-wrap justify-end gap-2 sm:w-auto">
                <flux:button type="button" variant="ghost" :href="route('papers.index')" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save">Save draft</flux:button>
            </div>
        </div>
    </form>
</div>
