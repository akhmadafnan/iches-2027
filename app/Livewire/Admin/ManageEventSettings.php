<?php

namespace App\Livewire\Admin;

use App\Models\EventMilestone;
use App\Models\EventSetting;
use DateTimeZone;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Event Settings | ICHES 2027')]
class ManageEventSettings extends Component
{
    use WithPagination;

    public string $name = '';

    public string $shortName = '';

    public string $theme = '';

    public string $organizer = '';

    public string $mainVenue = '';

    public string $startsOn = '';

    public string $endsOn = '';

    public string $startTime = '';

    public string $timezone = 'Asia/Jakarta';

    public ?int $editingMilestoneId = null;

    public bool $showMilestoneModal = false;

    public string $milestoneName = '';

    public string $milestoneStartsOn = '';

    public string $milestoneEndsOn = '';

    public string $milestoneNotes = '';

    public function mount(): void
    {
        Gate::authorize('manage-event-settings');

        $setting = EventSetting::query()->find(1);

        if ($setting === null) {
            return;
        }

        $this->name = $setting->name;
        $this->shortName = $setting->short_name;
        $this->theme = $setting->theme ?? '';
        $this->organizer = $setting->organizer ?? '';
        $this->mainVenue = $setting->main_venue ?? '';
        $this->startsOn = $setting->starts_on->format('Y-m-d');
        $this->endsOn = $setting->ends_on->format('Y-m-d');
        $this->startTime = $setting->start_time !== null ? substr($setting->start_time, 0, 5) : '';
        $this->timezone = $setting->timezone;
    }

    public function save(): void
    {
        Gate::authorize('manage-event-settings');

        $data = $this->validate([
            'name' => ['required', 'string', 'min:5', 'max:240'],
            'shortName' => ['required', 'string', 'max:80'],
            'theme' => ['nullable', 'string', 'max:2000'],
            'organizer' => ['nullable', 'string', 'max:240'],
            'mainVenue' => ['nullable', 'string', 'max:255'],
            'startsOn' => ['required', 'date_format:Y-m-d'],
            'endsOn' => ['required', 'date_format:Y-m-d', 'after_or_equal:startsOn'],
            'startTime' => ['nullable', 'date_format:H:i'],
            'timezone' => ['required', Rule::in(DateTimeZone::listIdentifiers())],
        ]);

        $setting = EventSetting::query()->find(1) ?? new EventSetting;
        $setting->id = 1;
        $setting->fill([
            'name' => $data['name'],
            'short_name' => $data['shortName'],
            'theme' => filled($data['theme'] ?? null) ? $data['theme'] : null,
            'organizer' => filled($data['organizer'] ?? null) ? $data['organizer'] : null,
            'main_venue' => filled($data['mainVenue'] ?? null) ? $data['mainVenue'] : null,
            'starts_on' => $data['startsOn'],
            'ends_on' => $data['endsOn'],
            'start_time' => filled($data['startTime'] ?? null) ? $data['startTime'] : null,
            'timezone' => $data['timezone'],
        ]);
        $setting->save();

        Flux::toast(variant: 'success', text: 'Event settings saved.');
    }

    public function editMilestone(int $id): void
    {
        Gate::authorize('manage-event-settings');

        $milestone = EventMilestone::query()->findOrFail($id);

        $this->editingMilestoneId = $milestone->id;
        $this->milestoneName = $milestone->name;
        $this->milestoneStartsOn = $milestone->starts_on?->format('Y-m-d') ?? '';
        $this->milestoneEndsOn = $milestone->ends_on?->format('Y-m-d') ?? '';
        $this->milestoneNotes = $milestone->notes ?? '';
        $this->resetValidation();
        $this->showMilestoneModal = true;
    }

    public function cancelMilestone(): void
    {
        $this->editingMilestoneId = null;
        $this->showMilestoneModal = false;
        $this->resetValidation();
    }

    public function saveMilestone(): void
    {
        Gate::authorize('manage-event-settings');

        $data = $this->validate([
            'editingMilestoneId' => ['required', 'integer', 'exists:event_milestones,id'],
            'milestoneName' => ['required', 'string', 'min:3', 'max:180'],
            'milestoneStartsOn' => ['nullable', 'required_with:milestoneEndsOn', 'date_format:Y-m-d'],
            'milestoneEndsOn' => ['nullable', 'required_with:milestoneStartsOn', 'date_format:Y-m-d', 'after_or_equal:milestoneStartsOn'],
            'milestoneNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $milestone = EventMilestone::query()->findOrFail((int) $data['editingMilestoneId']);
        $milestone->update([
            'name' => $data['milestoneName'],
            'starts_on' => filled($data['milestoneStartsOn'] ?? null) ? $data['milestoneStartsOn'] : null,
            'ends_on' => filled($data['milestoneEndsOn'] ?? null) ? $data['milestoneEndsOn'] : null,
            'notes' => filled($data['milestoneNotes'] ?? null) ? $data['milestoneNotes'] : null,
        ]);

        $this->cancelMilestone();
        Flux::toast(variant: 'success', text: 'Important date updated.');
    }

    public function render(): View
    {
        Gate::authorize('manage-event-settings');

        return view('livewire.admin.manage-event-settings', [
            'milestones' => EventMilestone::query()->orderBy('sort_order')->orderBy('id')->paginate(10),
            'hasExactStartTime' => filled($this->startTime),
        ]);
    }
}
