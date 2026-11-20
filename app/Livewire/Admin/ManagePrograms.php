<?php

namespace App\Livewire\Admin;

use App\Models\Program;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Programs | ICHES 2027')]
class ManagePrograms extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingProgramId = null;

    public string $editingCode = '';

    public bool $showEditModal = false;

    public string $name = '';

    public string $description = '';

    public string $venue = '';

    public string $deliveryMode = '';

    public string $startsOn = '';

    public string $endsOn = '';

    public int $sortOrder = 1;

    public function mount(): void
    {
        Gate::authorize('manage-programs');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-programs');

        $program = Program::query()->findOrFail($id);

        $this->editingProgramId = $program->id;
        $this->editingCode = $program->code->value;
        $this->name = $program->name;
        $this->description = $program->description ?? '';
        $this->venue = $program->venue ?? '';
        $this->deliveryMode = $program->delivery_mode ?? '';
        $this->startsOn = $program->starts_on?->format('Y-m-d') ?? '';
        $this->endsOn = $program->ends_on?->format('Y-m-d') ?? '';
        $this->sortOrder = $program->sort_order;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function cancel(): void
    {
        $this->editingProgramId = null;
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('manage-programs');

        $validated = $this->validate([
            'editingProgramId' => ['required', 'integer', 'exists:programs,id'],
            'name' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'venue' => ['nullable', 'string', 'max:255'],
            'deliveryMode' => ['nullable', Rule::in(['onsite', 'online', 'hybrid'])],
            'startsOn' => ['nullable', 'required_with:endsOn', 'date_format:Y-m-d'],
            'endsOn' => ['nullable', 'required_with:startsOn', 'date_format:Y-m-d', 'after_or_equal:startsOn'],
            'sortOrder' => ['required', 'integer', 'between:1,99'],
        ]);

        $program = Program::query()->findOrFail((int) $validated['editingProgramId']);

        // Stable program code is never edited, reissued or deleted by the admin UI.
        $program->update([
            'name' => $validated['name'],
            'description' => filled($validated['description'] ?? null) ? $validated['description'] : null,
            'venue' => filled($validated['venue'] ?? null) ? $validated['venue'] : null,
            'delivery_mode' => filled($validated['deliveryMode'] ?? null) ? $validated['deliveryMode'] : null,
            'starts_on' => filled($validated['startsOn'] ?? null) ? $validated['startsOn'] : null,
            'ends_on' => filled($validated['endsOn'] ?? null) ? $validated['endsOn'] : null,
            'sort_order' => $validated['sortOrder'],
        ]);

        $this->cancel();
        Flux::toast(variant: 'success', text: 'Program details updated.');
    }

    public function render(): View
    {
        Gate::authorize('manage-programs');

        return view('livewire.admin.manage-programs', [
            'programs' => Program::query()
                ->when(trim($this->search) !== '', function (Builder $query): void {
                    $term = '%'.trim($this->search).'%';

                    $query->where(function (Builder $filter) use ($term): void {
                        $filter->where('name', 'like', $term)
                            ->orWhere('code', 'like', $term)
                            ->orWhere('venue', 'like', $term);
                    });
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(10),
            'totalPrograms' => Program::query()->count(),
        ]);
    }
}
