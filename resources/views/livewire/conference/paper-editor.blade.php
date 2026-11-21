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
                        <p class="mt-1 text-sm text-zinc-500">Contributors in publication order. Use Edit to view or change their details.</p>
                    </div>
                </div>
                <flux:button type="button" size="sm" variant="outline" icon="plus" wire:click="addAuthor">Add author</flux:button>
            </div>

            <div class="divide-y divide-zinc-100 px-5 dark:divide-zinc-800 sm:px-6">
                @foreach ($authors as $index => $author)
                    <div wire:key="author-summary-{{ $author['key'] }}" class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:gap-4">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-xs font-semibold tabular-nums text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold leading-6 text-zinc-900 dark:text-white">{{ $author['name'] ?: 'Unnamed author' }}</p>
                                    @if ($correspondingIndex === $index)
                                        <flux:badge color="emerald" size="sm">Corresponding</flux:badge>
                                    @endif
                                </div>
                                <p class="break-words text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $author['institution'] ?: 'Affiliation not selected — edit to choose an institution' }}</p>
                                @if ($errors->has("authors.{$index}.affiliation_source") || $errors->has("authors.{$index}.ror_id") || $errors->has("authors.{$index}.institution"))
                                    <p class="text-xs text-red-600">Please complete or verify this contributor's institution.</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center justify-end gap-1 sm:justify-start">
                            @if ($correspondingIndex !== $index)
                                <flux:button type="button" size="sm" variant="ghost" icon="check-circle" wire:click="setCorrespondingAuthor({{ $index }})" tooltip="Make corresponding author" aria-label="Make author {{ $index + 1 }} corresponding" />
                            @endif
                            <flux:button type="button" size="sm" variant="ghost" icon="arrow-up" wire:click="moveAuthor({{ $index }}, 'up')" :disabled="$index === 0" tooltip="Move up" aria-label="Move author {{ $index + 1 }} up" />
                            <flux:button type="button" size="sm" variant="ghost" icon="arrow-down" wire:click="moveAuthor({{ $index }}, 'down')" :disabled="$index === count($authors) - 1" tooltip="Move down" aria-label="Move author {{ $index + 1 }} down" />
                            <flux:button type="button" size="sm" variant="outline" icon="pencil-square" wire:click="editAuthor({{ $index }})" aria-label="Edit author {{ $index + 1 }}">Edit</flux:button>
                            <flux:button type="button" size="sm" variant="ghost" icon="trash" wire:click="removeAuthor({{ $index }})" wire:confirm="Remove this author from the draft? Changes take effect when the paper is saved." :disabled="count($authors) === 1" tooltip="Remove author" aria-label="Remove author {{ $index + 1 }}" />
                        </div>
                    </div>
                @endforeach
            </div>

            @error('authors') <p class="px-6 pb-3 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('correspondingIndex') <p class="px-6 pb-3 text-sm text-red-600">{{ $message }}</p> @enderror
            <div class="border-t border-zinc-100 bg-zinc-50/50 px-6 py-3 text-xs leading-5 text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950/30">
                Exactly one corresponding author is designated. Contributor edits are included when you save the paper Draft.
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-100 px-6 py-5 dark:border-zinc-800">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400"><flux:icon.document-text class="size-5" /></div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Abstract &amp; keywords</h2>
                    <p class="mt-1 text-sm text-zinc-500">Add the academic abstract and register each keyword individually, as in OJS.</p>
                </div>
            </div>

            <div class="space-y-6 p-5 sm:p-6">
                <div class="space-y-2">
                    <flux:textarea wire:model="abstractText" label="Abstract text" placeholder="Enter or paste the abstract of your research..." rows="8" maxlength="10000" />
                    <p class="text-xs leading-5 text-zinc-500">You may leave this unfinished while saving a Draft. A 10,000-character technical safeguard applies; the official academic word limit will be configured before submission opens.</p>
                </div>

                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Keywords</p>
                        <span class="text-xs font-medium tabular-nums text-zinc-500">{{ count($keywords) }} of 15 tags</span>
                    </div>
                    @if (count($keywords) > 0)
                        <div class="flex flex-wrap gap-2 rounded-xl border border-emerald-200 bg-emerald-50/35 p-3 dark:border-emerald-900 dark:bg-emerald-950/20" aria-label="Saved keywords">
                            @foreach ($keywords as $index => $keyword)
                                <div wire:key="paper-keyword-{{ $index }}" class="inline-flex max-w-full items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-medium text-emerald-900 shadow-sm dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                                    <span class="break-words">{{ $keyword }}</span>
                                    <button type="button" wire:click="removeKeyword({{ $index }})" class="flex size-5 shrink-0 items-center justify-center rounded text-emerald-700 hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 dark:text-emerald-300" aria-label="Remove keyword {{ $keyword }}"><flux:icon.x-mark class="size-3.5" /></button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                        <div class="min-w-0 flex-1">
                            <flux:input wire:model="keywordInput" wire:keydown.enter.prevent="addKeyword" label="Add one keyword" placeholder="e.g. Islamic Education — press Enter" autocomplete="off" maxlength="80" aria-label="Add one keyword" />
                        </div>
                        <flux:button type="button" variant="outline" icon="plus" wire:click="addKeyword" class="w-full sm:w-auto">Add keyword</flux:button>
                    </div>
                    <p class="text-xs leading-5 text-zinc-500">Press Enter or click Add keyword after each word or phrase. Duplicate keywords are blocked. No comma-separated text list is used.</p>
                    @error('keywords') <p role="alert" class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('keywords.*') <p role="alert" class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-100 px-6 py-5 dark:border-zinc-800">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700 dark:bg-violet-950 dark:text-violet-400"><flux:icon.document-text class="size-5" /></div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Abstract PDF</h2>
                    <p class="mt-1 text-sm text-zinc-500">Private file, accessible only to the paper owner. No reviewer access in Draft status.</p>
                </div>
            </div>

            <div class="space-y-4 p-5 sm:p-6">
                @if ($currentAbstractPdfName !== null && ! $removeAbstractPdf)
                    <div class="flex flex-col gap-3 rounded-xl border border-emerald-200 bg-emerald-50/65 p-4 dark:border-emerald-900 dark:bg-emerald-950/20 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-start gap-3">
                            <flux:icon.check-circle class="mt-0.5 size-5 shrink-0 text-emerald-700" />
                            <div class="min-w-0 space-y-1">
                                <p class="break-all text-sm font-semibold text-emerald-950 dark:text-emerald-100">{{ $currentAbstractPdfName }}</p>
                                <p class="text-xs text-emerald-800 dark:text-emerald-300">{{ $currentAbstractPdfSize !== null ? number_format($currentAbstractPdfSize / 1024, 1).' KB' : 'PDF' }} · Uploaded {{ $currentAbstractPdfUploadedAt ?? 'previously' }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <flux:button type="button" variant="outline" size="sm" :href="route('papers.abstract.download', $editingPublicId)">Download</flux:button>
                            <flux:button type="button" variant="ghost" size="sm" icon="trash" wire:click="markPdfForRemoval">Remove</flux:button>
                        </div>
                    </div>
                @elseif ($removeAbstractPdf)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-950/20">
                        <p class="text-sm text-amber-900 dark:text-amber-100">The existing PDF will be removed when you save this Draft.</p>
                        <flux:button type="button" variant="ghost" size="sm" wire:click="undoPdfRemoval">Undo removal</flux:button>
                    </div>
                @endif

                <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50/60 p-4 dark:border-zinc-700 dark:bg-zinc-800/30">
                    <flux:input type="file" wire:model="abstractPdfUpload" :label="$currentAbstractPdfName !== null ? 'Choose a replacement PDF' : 'Upload abstract PDF'" accept=".pdf,application/pdf" />
                    <div wire:loading wire:target="abstractPdfUpload" class="mt-2 flex items-center gap-2 text-xs text-emerald-700">
                        <flux:icon.arrow-path class="size-4 animate-spin" /> Uploading PDF to temporary private storage...
                    </div>
                    @if ($abstractPdfUpload)
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="min-w-0">
                                <p class="break-all text-sm font-medium text-zinc-800 dark:text-white">{{ $abstractPdfUpload->getClientOriginalName() }}</p>
                                <p class="text-xs text-zinc-500">{{ number_format(($abstractPdfUpload->getSize() ?: 0) / 1024, 1) }} KB · Selected, not yet saved</p>
                            </div>
                            <flux:button type="button" variant="ghost" size="sm" wire:click="discardSelectedPdf" icon="x-mark">Clear</flux:button>
                        </div>
                    @endif
                    <p class="mt-3 text-xs leading-5 text-zinc-500">PDF only, up to 10 MB (technical limit). The file is stored privately when you click Save Draft. You may save a Draft without a PDF.</p>
                </div>
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

    @include('livewire.conference.partials.author-modal')
</div>
