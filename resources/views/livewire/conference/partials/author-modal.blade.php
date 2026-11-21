<flux:modal wire:model.self="authorModalOpen" class="md:w-[43rem]">
    @if ($authorModalOpen)
        <form wire:submit="saveAuthor" class="space-y-5">
            <div class="space-y-1">
                <flux:heading size="xl">{{ $editingAuthorIndex === null ? 'Add author' : 'Edit author' }}</flux:heading>
                <flux:text size="sm">Record complete contributor details here; only the name and affiliation are shown in the paper directory.</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="authorDraft.name" label="Full name" placeholder="Full author name" required />
                <flux:input wire:model="authorDraft.email" type="email" label="Email address" placeholder="author@example.com" required />
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Institution / affiliation <span class="text-red-600">*</span></p>
                    @if (($authorDraft['affiliation_source'] ?? '') === 'manual')
                        <flux:button type="button" size="sm" variant="ghost" icon="magnifying-glass" wire:click="useRorAffiliation">Search ROR instead</flux:button>
                    @elseif (($authorDraft['affiliation_source'] ?? '') === 'ror')
                        <flux:badge color="emerald" size="sm">ROR verified</flux:badge>
                    @endif
                </div>

                @if (($authorDraft['affiliation_source'] ?? '') === 'manual')
                    <div class="space-y-2 rounded-xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-900 dark:bg-amber-950/20">
                        <flux:input wire:model="authorDraft.institution" label="Official institution name (not in ROR)" placeholder="Enter complete institution name" required />
                        <p class="text-xs leading-5 text-amber-800 dark:text-amber-200">Unverified institution. No ROR ID is assigned; select this option only when the organization genuinely cannot be found.</p>
                    </div>
                @else
                    <div class="relative">
                        <flux:input
                            wire:model.live.debounce.450ms="authorDraft.institution_query"
                            label="Search ROR by university name or acronym"
                            icon="magnifying-glass"
                            placeholder="e.g. UNISYA, Universitas Islam Syarifuddin"
                            autocomplete="off"
                            aria-label="Search ROR institution"
                        />
                        <div wire:loading wire:target="authorDraft.institution_query" class="mt-2 text-xs text-zinc-500">Searching ROR...</div>

                        @if (count($affiliationResults) > 0 && ($authorDraft['affiliation_source'] ?? '') !== 'ror')
                            <div class="absolute inset-x-0 top-full z-40 mt-1 max-h-52 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1.5 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                                @foreach ($affiliationResults as $result)
                                    <button type="button" wire:key="ror-modal-{{ str_replace('https://ror.org/', '', $result['id']) }}"
                                        wire:click="selectInstitution(@js($result['id']))"
                                        class="flex w-full flex-col items-start gap-1 rounded-lg px-3 py-2.5 text-left transition hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none dark:hover:bg-emerald-950/40 dark:focus:bg-emerald-950/40">
                                        <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $result['name'] }}</span>
                                        <span class="text-xs text-zinc-500">{{ $result['aliases'] ? $result['aliases'].' · ' : '' }}{{ $result['location'] }} · {{ str_replace('https://ror.org/', '', $result['id']) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if (($authorDraft['affiliation_source'] ?? '') === 'ror' && filled($authorDraft['ror_id'] ?? null))
                        <div class="flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900 dark:bg-emerald-950/20">
                            <div>
                                <p class="text-sm font-semibold leading-6 text-emerald-950 dark:text-emerald-200">{{ $authorDraft['institution'] }}</p>
                                <p class="mt-1 text-xs text-emerald-800 dark:text-emerald-300">Verified ROR ID:
                                    <a class="underline underline-offset-2" href="{{ $authorDraft['ror_id'] }}" target="_blank" rel="noopener noreferrer">{{ str_replace('https://ror.org/', '', $authorDraft['ror_id']) }}</a>
                                </p>
                            </div>
                            <flux:icon.check-circle class="mt-1 size-5 shrink-0 text-emerald-700" />
                        </div>
                    @elseif (($authorDraft['affiliation_source'] ?? '') === 'legacy')
                        <p class="text-xs leading-5 text-amber-700 dark:text-amber-300">This historical affiliation has no verified ROR ID. Please choose the organization or explicitly mark it unlisted.</p>
                    @else
                        <p class="text-xs leading-5 text-zinc-500">Select the precise organization from the results. Typing a name alone does not verify it.</p>
                    @endif

                    @if ($affiliationError !== '')
                        <p role="alert" class="text-xs text-red-600">{{ $affiliationError }}</p>
                    @elseif (count($affiliationResults) === 0 && $affiliationSearched && ($authorDraft['affiliation_source'] ?? '') !== 'ror')
                        <p class="text-xs text-zinc-500">No active ROR match. Check spelling or try the organization acronym.</p>
                    @endif

                    @if (($authorDraft['affiliation_source'] ?? '') !== 'ror')
                        <flux:button type="button" variant="ghost" size="sm" icon="information-circle" wire:click="useManualAffiliation">Institution not listed in ROR</flux:button>
                    @endif
                @endif
                @error('authorDraft.affiliation_source') <p role="alert" class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('authorDraft.ror_id') <p role="alert" class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('authorDraft.institution') <p role="alert" class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="authorDraft.country" label="Author country (optional)" placeholder="Country" />
                <div class="flex flex-col justify-center gap-2 rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:checkbox wire:model="authorDraftCorresponding" label="Corresponding author"
                        :disabled="$editingAuthorIndex !== null && $editingAuthorIndex === $correspondingIndex" />
                    <p class="text-xs leading-5 text-zinc-500">Only one corresponding author per paper. You can also designate another author from the list.</p>
                </div>
            </div>
            @error('authorDraftCorresponding') <p role="alert" class="text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                <flux:button type="button" variant="ghost" wire:click="cancelAuthor">Cancel</flux:button>
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="saveAuthor">
                    {{ $editingAuthorIndex === null ? 'Add author to list' : 'Save author' }}
                </flux:button>
            </div>
            <p class="text-xs leading-5 text-zinc-500">Contributor changes are held in this Draft form until you click Save draft on the paper page.</p>
        </form>
    @endif
</flux:modal>
