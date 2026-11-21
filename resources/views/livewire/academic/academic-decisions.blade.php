<div class="mx-auto w-full max-w-[1380px] space-y-6 pb-14">
    <section class="relative overflow-hidden rounded-[1.5rem] bg-[#104b3c] px-6 py-8 text-white shadow-lg sm:px-9">
        <p class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-emerald-200"><flux:icon.academic-cap class="size-4" /> ICHES 2027 / Academic Authority</p>
        <h1 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Academic Decisions</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-emerald-50/85">Review finalized recommendations, approve any exceptions, then Publish &amp; Notify eligible paper owners together. Internal reviewer decisions remain private until publication.</p>
    </section>

    @if (session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">{{ session('success') }}</div>
    @endif
    @if ($lastResult !== null)
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">
            <strong>Batch completed:</strong> {{ $lastResult['published'] }} published, {{ $lastResult['skipped'] }} skipped, {{ $lastResult['failed'] }} failed. {{ $lastResult['notifications'] }} personal emails queued. Email delivery is asynchronous.
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs text-zinc-500">Ready for publication</p><p class="mt-1 text-3xl font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">{{ $readyCount }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs text-zinc-500">Awaiting final review</p><p class="mt-1 text-3xl font-semibold tabular-nums">{{ $awaitingCount }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs text-zinc-500">Published decisions</p><p class="mt-1 text-3xl font-semibold tabular-nums">{{ $publishedCount }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 p-5 dark:border-zinc-800">
            <flux:button size="sm" :variant="$tab === 'ready' ? 'primary' : 'outline'" wire:click="selectTab('ready')">Ready ({{ $readyCount }})</flux:button>
            <flux:button size="sm" :variant="$tab === 'awaiting' ? 'primary' : 'outline'" wire:click="selectTab('awaiting')">Awaiting ({{ $awaitingCount }})</flux:button>
            <flux:button size="sm" :variant="$tab === 'published' ? 'primary' : 'outline'" wire:click="selectTab('published')">Published ({{ $publishedCount }})</flux:button>
            <div class="w-full sm:ml-auto sm:w-60">
                <flux:input size="sm" wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Paper ID or title" aria-label="Search academic papers" />
            </div>
            <div class="w-full sm:w-56">
                <flux:select size="sm" wire:model.live="trackFilter" aria-label="Filter conference track">
                    <flux:select.option value="">All conference tracks</flux:select.option>
                    @foreach ($tracks as $track)
                        <flux:select.option :value="(string) $track->id">{{ $track->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if ($tab === 'ready')
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 bg-emerald-50/40 px-5 py-4 dark:border-zinc-800 dark:bg-emerald-950/20">
                <div class="space-y-1">
                    <p class="text-sm font-semibold">Select papers using checkboxes</p>
                    <p class="text-xs text-zinc-600 dark:text-zinc-300"><strong class="text-emerald-800 dark:text-emerald-300">{{ count($selectedIds) }} selected</strong> across pages; only checked papers are included in <strong>Publish Selected</strong>.</p>
                    <p class="text-xs text-zinc-500">Header checkbox selects or deselects only the 10 papers on this page. All final reviewer General Comments are automatically shared with their author only when a decision is published.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($selectedIds !== [])
                        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="clearSelection">Clear selection</flux:button>
                    @endif
                    <flux:button size="sm" variant="primary" icon="check" wire:click="askPublish(false)" :disabled="$selectedIds === []">Publish Selected ({{ count($selectedIds) }})</flux:button>
                    <flux:button size="sm" variant="outline" icon="exclamation-triangle" wire:click="askPublish(true)" :disabled="$readyCount === 0">Publish All Ready ({{ $readyCount }})</flux:button>
                </div>
            </div>
            <p class="border-b border-zinc-100 px-5 py-2 text-xs text-zinc-500 dark:border-zinc-800">Publish All Ready ignores checkboxes, search, pagination and track filters. It processes EVERY eligible paper after a separate typed confirmation.</p>
        @endif

        @error('publish')<p role="alert" class="px-5 pt-3 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('notification')<p role="alert" class="px-5 pt-3 text-sm text-red-600">{{ $message }}</p>@enderror

        <p class="px-5 pt-3 text-xs text-zinc-500 md:hidden"><flux:icon.arrows-right-left class="mr-1 inline size-3.5" /> Swipe within the table to see its columns.</p>
        <div class="overflow-x-auto px-3 pb-2">
            <flux:table class="w-full min-w-[790px] lg:min-w-0">
                @php
                    $visiblePageIds = $tab === 'ready' ? $papers->getCollection()->pluck('public_id')->all() : [];
                    $allVisibleSelected = $visiblePageIds !== [] && count(array_diff($visiblePageIds, $selectedIds)) === 0;
                @endphp
                <flux:table.columns>
                    @if ($tab === 'ready')
                        <flux:table.column class="w-16">
                            <label class="inline-flex cursor-pointer items-center gap-2" title="Select or deselect all papers on this page">
                                <input type="checkbox" class="size-4 cursor-pointer rounded border-zinc-300 accent-emerald-700 focus:ring-2 focus:ring-emerald-500" @checked($allVisibleSelected) @disabled($visiblePageIds === []) wire:click="toggleCurrentPage" aria-label="Select all papers on this page" />
                                <span class="sr-only">Select all papers on this page</span>
                            </label>
                        </flux:table.column>
                    @endif
                    <flux:table.column class="w-36">Paper ID</flux:table.column>
                    <flux:table.column>Title / track</flux:table.column>
                    <flux:table.column class="w-32">{{ $tab === 'ready' ? 'Proposed' : 'Status' }}</flux:table.column>
                    <flux:table.column class="w-40">{{ $tab === 'published' ? 'Notification' : 'Academic stage' }}</flux:table.column>
                    <flux:table.column class="w-52" align="end">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($papers as $paper)
                        @php
                            $finalReview = $paper->reviews->first(fn ($r) => $r->status === \App\Enums\PaperReviewStatus::Submitted && $r->claim?->active_paper_id === $paper->id);
                            $proposed = $finalReview?->recommendation?->value === 'accept' ? 'accepted' : 'rejected';
                            $decision = $paper->decision;
                            $notice = $paper->decisionNotifications->first();
                        @endphp
                        <flux:table.row :key="$paper->id" :class="$tab === 'ready' && in_array($paper->public_id, $selectedIds, true) ? 'bg-emerald-50/70 dark:bg-emerald-950/20' : ''">
                            @if ($tab === 'ready')
                                <flux:table.cell>
                                    <label class="inline-flex cursor-pointer items-center p-1" title="Select {{ $paper->reference }}">
                                        <input type="checkbox" class="size-4 cursor-pointer rounded border-zinc-300 accent-emerald-700 focus:ring-2 focus:ring-emerald-500" @checked(in_array($paper->public_id, $selectedIds, true)) wire:click="toggleSelected('{{ $paper->public_id }}')" aria-label="Select {{ $paper->reference }}" />
                                    </label>
                                </flux:table.cell>
                            @endif
                            <flux:table.cell class="font-mono text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $paper->reference }}</flux:table.cell>
                            <flux:table.cell>
                                <p class="max-w-lg break-words text-sm font-semibold">{{ $paper->title }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $paper->conferenceTrack?->name }}</p>
                                @if ($tab === 'ready' && filled($finalReview?->general_comments))
                                    <details class="mt-2 max-w-md text-xs">
                                        <summary class="cursor-pointer font-medium text-emerald-700 dark:text-emerald-400">Lihat General Comments (otomatis dibagikan saat Publish)</summary>
                                        <p class="mt-2 whitespace-pre-line break-words text-zinc-600 dark:text-zinc-300">{{ $finalReview->general_comments }}</p>
                                    </details>
                                    <p class="mt-2 inline-flex items-start gap-1.5 text-xs font-medium leading-5 text-emerald-700 dark:text-emerald-300">
                                        <flux:icon.check-circle class="mt-0.5 size-3.5 shrink-0" />
                                        Komentar otomatis ditampilkan kepada author setelah Publish &amp; Notify.
                                    </p>
                                @endif
                                @if ($tab === 'ready' && isset($overrideMap[$paper->public_id]))
                                    <flux:badge color="amber" size="sm" class="mt-2">Override: {{ ucfirst($overrideMap[$paper->public_id]['outcome']) }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($tab === 'ready')
                                    <flux:badge :color="$proposed === 'accepted' ? 'emerald' : 'rose'" size="sm">{{ ucfirst($proposed) }}</flux:badge>
                                @elseif ($tab === 'published')
                                    <flux:badge :color="$decision?->outcome === 'accepted' ? 'emerald' : 'rose'" size="sm">{{ ucfirst($decision?->outcome ?? '-') }}</flux:badge>
                                    <p class="mt-1 text-xs text-zinc-500">Version {{ $decision?->version }}</p>
                                @else
                                    <flux:badge color="amber" size="sm">Submitted</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($tab === 'ready')
                                    <p class="text-xs text-zinc-500">Review submitted</p>
                                    <p class="mt-1 text-xs">{{ $finalReview?->submitted_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                                @elseif ($tab === 'published')
                                    <flux:badge :color="$notice?->status === 'sent' ? 'emerald' : ($notice?->status === 'failed' ? 'rose' : 'amber')" size="sm">{{ ucfirst($notice?->status === 'processing' ? 'queued' : ($notice?->status ?? 'queued')) }}</flux:badge>
                                    <p class="mt-1 text-xs text-zinc-500">Version {{ $notice?->decision_version }} / {{ $notice?->attempts }} attempt(s)</p>
                                @else
                                    <span class="text-xs text-zinc-500">Awaiting final review</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                @if ($tab === 'ready')
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <flux:button size="sm" variant="outline" icon="pencil-square" wire:click="openOverride(@js($paper->public_id))" :aria-label="'Override proposed decision '.$paper->reference">Override</flux:button>
                                        @if (isset($overrideMap[$paper->public_id]))
                                            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="removeOverride(@js($paper->public_id))" :aria-label="'Remove override '.$paper->reference" />
                                        @endif
                                    </div>
                                @elseif ($tab === 'published')
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        @if ($notice !== null && in_array($notice->status, ['failed', 'queued'], true))
                                            <flux:button size="sm" variant="outline" icon="arrow-path" wire:click="retryNotification(@js($notice->id))" :aria-label="'Requeue notification '.$paper->reference">Requeue</flux:button>
                                        @endif
                                        <flux:button size="sm" variant="outline" icon="pencil-square" wire:click="openCorrection(@js($paper->public_id))" :aria-label="'Correct published decision '.$paper->reference">Correct</flux:button>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">Not eligible</span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell :colspan="$tab === 'ready' ? 6 : 5">
                                <div class="flex min-h-48 flex-col items-center justify-center gap-2 text-center">
                                    <flux:icon.inbox class="size-8 text-zinc-400" />
                                    <p class="text-sm font-semibold">No {{ $tab }} papers found</p>
                                    <p class="text-xs text-zinc-500">Adjust the search/filter or wait for a reviewer to submit a final review.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 p-5 text-xs text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
            <span>Showing {{ $papers->firstItem() ?? 0 }}–{{ $papers->lastItem() ?? 0 }} of {{ $papers->total() }} papers</span>
            <div class="flex gap-2">
                <flux:button size="sm" variant="outline" icon="chevron-left" wire:click="previousPage" :disabled="$papers->onFirstPage()">Previous</flux:button>
                <flux:button size="sm" variant="outline" wire:click="nextPage" :disabled="! $papers->hasMorePages()" icon:trailing="chevron-right">Next</flux:button>
            </div>
        </div>
    </section>

    <div class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
        <p>Publication is not an email delivery receipt. A Published decision appears immediately to the owner; an outbox status remains Queued until processed by a worker. All submitted General Comments are shared automatically on publication; reviewer identity, original recommendation and internal reasons remain private. LoA, Finance and Full Paper are separate future workflows.</p>
    </div>

    <flux:modal wire:model.self="overrideModalOpen" class="md:w-[34rem]">
        @if ($overrideModalOpen)
            <div class="space-y-5">
                <div><flux:heading size="xl">Exception override</flux:heading><flux:text size="sm">Paper {{ $overrideReference }}. The original reviewer recommendation remains unchanged.</flux:text></div>
                <flux:select wire:model="overrideTarget" label="Proposed exceptional decision">
                    <flux:select.option value="accepted">Accepted</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
                <flux:textarea wire:model="overrideReason" rows="4" label="Internal justification (required)" placeholder="Explain the academic reason for changing the reviewer recommendation." />
                @error('override')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button variant="ghost" wire:click="cancelOverride">Cancel</flux:button>
                    <flux:button variant="primary" icon="check" wire:click="saveOverride">Stage override</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal wire:model.self="publishModalOpen" class="md:w-[35rem]">
        @if ($publishModalOpen)
            <div class="space-y-5">
                <div>
                    <flux:heading size="xl">{{ $publishingAllReady ? 'Publish ALL Ready Papers?' : 'Publish Selected Papers?' }}</flux:heading>
                    <flux:text size="sm">{{ $publishingAllReady ? 'This includes all eligible papers across ALL pages, regardless of selection and filters.' : 'Only the checked paper IDs are included. All records are rechecked server-side.' }}</flux:text>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                    @foreach (['total' => 'Eligible', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'overridden' => 'Overrides', 'comments' => 'Reviewer comments included'] as $key => $label)
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800">
                            <p class="text-xs text-zinc-500">{{ $label }}</p><p class="mt-1 text-xl font-semibold tabular-nums">{{ $preview[$key] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Paper IDs included in this confirmation</p>
                    <p class="break-words font-mono text-xs leading-6 text-zinc-800 dark:text-zinc-200">{{ implode(', ', $confirmReferences) }}</p>
                    @if (count($confirmIds) > 20)<p class="text-xs text-amber-700">And {{ count($confirmIds) - 20 }} more. Cancel to review the full batch before publication.</p>@endif
                </div>
                @if ($publishingAllReady)
                    <div class="space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-950/30">
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Extra confirmation required for ALL eligible papers</p>
                        <p class="text-xs text-amber-800 dark:text-amber-300">Type <strong>PUBLISH ALL READY</strong> exactly. This publishes papers you have NOT checked.</p>
                        <flux:input wire:model="allReadyConfirmation" label="Type PUBLISH ALL READY" placeholder="PUBLISH ALL READY" autocomplete="off" />
                    </div>
                @endif
                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">Up to {{ $preview['total'] }} personal owner emails will enter the outbox. Skipped or changed records are reported separately. Published results are immediately visible to the corresponding owners; email delivery may be delayed.</p>
                @error('publish')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button variant="ghost" wire:click="cancelPublish">Cancel</flux:button>
                    <flux:button variant="primary" icon="paper-airplane" wire:click="confirmPublish" wire:loading.attr="disabled" wire:target="confirmPublish">Confirm Publish &amp; Notify</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal wire:model.self="correctionModalOpen" class="md:w-[35rem]">
        @if ($correctionModalOpen)
            <div class="space-y-5">
                <div><flux:heading size="xl">Correct published decision</flux:heading><flux:text size="sm">Paper {{ $correctionReference }}, current version {{ $correctionVersion }}. This action creates an immutable correction audit and a new personal notification.</flux:text></div>
                <flux:select wire:model="correctionTarget" label="Corrected published outcome">
                    <flux:select.option value="accepted">Accepted</flux:select.option>
                    <flux:select.option value="rejected">Rejected</flux:select.option>
                </flux:select>
                <p class="text-xs text-zinc-500">Reviewer General Comments stay visible to the author automatically and are not changed by this correction.</p>
                <flux:textarea wire:model="correctionReason" rows="4" label="Internal correction reason (required)" placeholder="Explain this exceptional change. This text is never shown to the paper owner." />
                @error('correction')<p role="alert" class="text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="text-xs text-amber-700 dark:text-amber-300">After future LoA/Finance modules exist, reversal may require document revocation and financial reconciliation. Those actions are not yet available.</p>
                <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <flux:button variant="ghost" wire:click="cancelCorrection">Cancel</flux:button>
                    <flux:button variant="primary" icon="check" wire:click="confirmCorrection" wire:loading.attr="disabled" wire:target="confirmCorrection">Confirm correction &amp; notify</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
