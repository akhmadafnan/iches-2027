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
                                <p class="whitespace-nowrap text-sm font-semibold text-zinc-900 dark:text-white">Author {{ $index + 1 }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-0 sm:gap-1">
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-up" wire:click="moveAuthor({{ $index }}, 'up')" :disabled="$index === 0" tooltip="Move author up" />
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-down" wire:click="moveAuthor({{ $index }}, 'down')" :disabled="$index === count($authors) - 1" tooltip="Move author down" />
                                <flux:button type="button" size="sm" variant="ghost" icon="trash" wire:click="removeAuthor({{ $index }})" :disabled="count($authors) === 1" tooltip="Remove author" aria-label="Remove author {{ $index + 1 }}" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:input wire:model="authors.{{ $index }}.name" label="Full name" placeholder="Author name" required />
                            <flux:input wire:model="authors.{{ $index }}.email" type="email" label="Email address" placeholder="author@example.com" required />
                        </div>

                        <div class="mt-5 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Institution / affiliation <span class="text-red-600">*</span></p>
                                @if (($author['affiliation_source'] ?? '') === 'manual')
                                    <flux:button type="button" size="sm" variant="ghost" icon="magnifying-glass" wire:click="useRorAffiliation({{ $index }})">Search ROR instead</flux:button>
                                @elseif (($author['affiliation_source'] ?? '') === 'ror')
                                    <flux:badge color="emerald" size="sm">ROR verified</flux:badge>
                                @endif
                            </div>

                            @if (($author['affiliation_source'] ?? '') === 'manual')
                                <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-900 dark:bg-amber-950/20">
                                    <flux:input wire:model="authors.{{ $index }}.institution" label="Official institution name (not in ROR)" placeholder="Enter the full organization name" required />
                                    <p class="mt-2 text-xs leading-5 text-amber-800 dark:text-amber-200">
                                        Unverified institution: no ROR ID will be assigned. Use only when the institution genuinely cannot be found in the registry.
                                    </p>
                                </div>
                            @else
                                <div class="relative">
                                    <flux:input
                                        wire:model.live.debounce.450ms="authors.{{ $index }}.institution_query"
                                        icon="magnifying-glass"
                                        label="Search ROR by full university name or acronym"
                                        placeholder="e.g. UNISYA, Universitas Islam Syarifuddin"
                                        autocomplete="off"
                                        aria-label="Search ROR institution for author {{ $index + 1 }}"
                                    />
                                    <div wire:loading wire:target="authors.{{ $index }}.institution_query" class="mt-2 text-xs text-zinc-500">Searching ROR...</div>
                                    @if (count($affiliationResults[$index] ?? []) > 0 && ($author['affiliation_source'] ?? '') !== 'ror')
                                        <div class="absolute inset-x-0 top-full z-30 mt-1 max-h-64 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1.5 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                                            @foreach ($affiliationResults[$index] as $result)
                                                <button type="button" wire:key="ror-{{ $index }}-{{ str_replace('https://ror.org/', '', $result['id']) }}"
                                                    wire:click="selectInstitution({{ $index }}, @js($result['id']))"
                                                    class="flex w-full flex-col items-start gap-1 rounded-lg px-3 py-2.5 text-left transition hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none dark:hover:bg-emerald-950/40 dark:focus:bg-emerald-950/40">
                                                    <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $result['name'] }}</span>
                                                    <span class="text-xs text-zinc-500">{{ $result['aliases'] ? $result['aliases'].' · ' : '' }}{{ $result['location'] }} · {{ str_replace('https://ror.org/', '', $result['id']) }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if (($author['affiliation_source'] ?? '') === 'ror' && filled($author['ror_id'] ?? null))
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50/80 p-3 dark:border-emerald-900 dark:bg-emerald-950/20">
                                        <div>
                                            <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">{{ $author['institution'] }}</p>
                                            <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">Verified ROR ID:
                                                <a class="underline underline-offset-2" href="{{ $author['ror_id'] }}" target="_blank" rel="noopener noreferrer">{{ str_replace('https://ror.org/', '', $author['ror_id']) }}</a>
                                            </p>
                                        </div>
                                        <flux:icon.check-circle class="size-5 shrink-0 text-emerald-700" />
                                    </div>
                                @elseif (($author['affiliation_source'] ?? '') === 'legacy')
                                    <p class="text-xs leading-5 text-amber-700 dark:text-amber-300">Previously entered affiliation has no verified ROR ID. Please select an organization or explicitly mark it as unlisted.</p>
                                @else
                                    <p class="text-xs leading-5 text-zinc-500">Select the exact organization from ROR suggestions; typing alone does not verify an affiliation.</p>
                                @endif
                                @if (filled($affiliationErrors[$index] ?? ''))
                                    <p role="alert" class="text-xs text-red-600">{{ $affiliationErrors[$index] }}</p>
                                @endif
                                @if (! count($affiliationResults[$index] ?? []) && ($affiliationSearched[$index] ?? false) && empty($affiliationErrors[$index]) && ($author['affiliation_source'] ?? '') !== 'ror')
                                    <p class="text-xs text-zinc-500">No active ROR match found. Check spelling, search an abbreviation, or use the unlisted option.</p>
                                @endif
                                @if (($author['affiliation_source'] ?? '') !== 'ror')
                                    <flux:button type="button" size="sm" variant="ghost" icon="information-circle" wire:click="useManualAffiliation({{ $index }})">Institution not listed in ROR</flux:button>
                                @endif
                            @endif
                            @error("authors.{$index}.affiliation_source") <p role="alert" class="text-xs text-red-600">{{ $message }}</p> @enderror
                            @error("authors.{$index}.ror_id") <p role="alert" class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-4">
                            <flux:input wire:model="authors.{{ $index }}.country" label="Author country (independent of institution)" placeholder="Country" />
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
