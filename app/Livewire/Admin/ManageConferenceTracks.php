<?php

namespace App\Livewire\Admin;

use App\Enums\ProgramCode;
use App\Models\Program;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Conference Tracks | ICHES 2027')]
class ManageConferenceTracks extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public ?int $editingTrackId = null;

    public bool $showEditModal = false;

    public string $editingCode = '';

    public string $officialTitle = '';

    public string $name = '';

    public string $description = '';

    public int $sortOrder = 1;

    public bool $isActive = true;

    public function mount(): void
    {
        Gate::authorize('manage-conference-tracks');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage-conference-tracks');

        $track = $this->conference()->conferenceTracks()->findOrFail($id);

        $this->editingTrackId = $track->id;
        $this->editingCode = $track->code;
        $this->officialTitle = $track->official_title;
        $this->name = $track->name;
        $this->description = $track->description ?? '';
        $this->sortOrder = $track->sort_order;
        $this->isActive = $track->is_active;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function cancel(): void
    {
        $this->editingTrackId = null;
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('manage-conference-tracks');

        $track = $this->conference()->conferenceTracks()->findOrFail($this->editingTrackId);

        $data = $this->validate([
            'name' => [
                'required', 'string', 'min:5', 'max:200',
                Rule::unique('conference_tracks', 'name')->ignore($track->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'sortOrder' => ['required', 'integer', 'between:1,99'],
            'isActive' => ['required', 'boolean'],
        ]);

        if (! $data['isActive'] && $track->is_active
            && $this->conference()->conferenceTracks()
                ->where('is_active', true)
                ->whereKeyNot($track->id)
                ->doesntExist()) {
            throw ValidationException::withMessages([
                'isActive' => 'At least one conference track must remain available for submissions.',
            ]);
        }

        // Immutable reference code, program ownership and TOR title are never mass-assigned.
        $track->update([
            'name' => trim($data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'sort_order' => $data['sortOrder'],
            'is_active' => $data['isActive'],
        ]);

        $this->cancel();
        Flux::toast(variant: 'success', text: 'Conference track updated.');
    }

    public function render(): View
    {
        Gate::authorize('manage-conference-tracks');

        $query = $this->conference()->conferenceTracks();

        return view('livewire.admin.manage-conference-tracks', [
            'tracks' => $query
                ->when(trim($this->search) !== '', function ($builder): void {
                    $term = '%'.trim($this->search).'%';
                    $builder->where(function ($filter) use ($term): void {
                        $filter->where('name', 'like', $term)
                            ->orWhere('official_title', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    });
                })
                ->when($this->statusFilter === 'active', fn ($builder) => $builder->where('is_active', true))
                ->when($this->statusFilter === 'inactive', fn ($builder) => $builder->where('is_active', false))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(10),
            'totalTracks' => $this->conference()->conferenceTracks()->count(),
            'activeTracks' => $this->conference()->conferenceTracks()->where('is_active', true)->count(),
        ]);
    }

    private function conference(): Program
    {
        return Program::query()->where('code', ProgramCode::Conference->value)->firstOrFail();
    }
}
