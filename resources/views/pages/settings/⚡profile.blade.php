<?php

use App\Concerns\ProfileValidationRules;
/* @chisel-email-verification */
use Illuminate\Contracts\Auth\MustVerifyEmail;
/* @end-chisel-email-verification */
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $institution = '';
    public string $country = '';

    public ?TemporaryUploadedFile $photo = null;

    public bool $requiresSetup = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->institution = Auth::user()->institution ?? '';
        $this->country = Auth::user()->country ?? '';
        $this->requiresSetup = (bool) Auth::user()->profile_setup_required;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        if (config('fortify.lowercase_usernames')) {
            $this->email = Str::lower($this->email);
        }

        $rules = $this->profileRules($user->id);
        $rules['photo'] = [
            ($user->profile_setup_required && blank($user->profile_photo_path)) ? 'required' : 'nullable',
            'image', 'mimes:jpg,jpeg,png,webp', 'max:3072',
            'dimensions:min_width=200,min_height=200',
        ];

        $validated = $this->validate($rules);
        $user->fill(collect($validated)->only(['name', 'email', 'institution', 'country'])->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $oldPhoto = $user->profile_photo_path;
        $newPhoto = null;

        if ($this->photo !== null) {
            $newPhoto = $this->photo->store('profile-photos/'.$user->id, 'local');

            if (! is_string($newPhoto)) {
                throw ValidationException::withMessages(['photo' => __('Photo could not be saved. Try again.')]);
            }

            $user->profile_photo_path = $newPhoto;
        }

        $onboardingComplete = $user->profile_setup_required && filled($user->profile_photo_path);

        if ($onboardingComplete) {
            $user->profile_setup_required = false;
        }

        $user->save();

        if ($newPhoto !== null && $oldPhoto !== null) {
            Storage::disk('local')->delete($oldPhoto);
        }

        $this->reset('photo');
        $this->requiresSetup = (bool) $user->profile_setup_required;

        if ($onboardingComplete) {
            $this->redirect(route('dashboard', absolute: false), navigate: true);

            return;
        }

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /* @chisel-email-verification */
    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
    /* @end-chisel-email-verification */
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Keep your personal information and photo up to date')">
        @if ($requiresSetup)
            <div role="status" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-5 dark:border-emerald-900 dark:bg-emerald-950/40">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">
                        <flux:icon.user-circle class="size-6" />
                    </div>
                    <div>
                        <flux:heading size="lg">Complete your ICHES profile</flux:heading>
                        <flux:text class="mt-1">Your account has been created. Please review your details and upload a profile photo to continue to the dashboard.</flux:text>
                    </div>
                </div>
            </div>
        @endif
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">Profile photo</flux:heading>
                <flux:text class="mt-1 text-sm">Upload a clear face photo (JPG, PNG or WebP, maximum 3 MB; at least 200 × 200 pixels). This will be used for your ICHES identity.</flux:text>
                <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950">
                        @if ($photo?->isPreviewable())
                            <img src="{{ $photo->temporaryUrl() }}" alt="New profile photo preview" class="size-full object-cover" />
                        @elseif ($photo)
                            <flux:icon.exclamation-triangle class="size-10 text-amber-500" />
                        @elseif (auth()->user()->profile_photo_path)
                            <img src="{{ route('profile.photo') }}" alt="Current profile photo" class="size-full object-cover" />
                        @else
                            <flux:icon.user class="size-12 text-emerald-300 dark:text-emerald-700" />
                        @endif
                    </div>
                    <div class="min-w-0 flex-1 space-y-2">
                        <flux:input wire:model="photo" type="file" accept="image/jpeg,image/png,image/webp" :label="__('Choose profile photo')" :required="$requiresSetup && ! auth()->user()->profile_photo_path" />
                        <flux:text size="sm" wire:loading wire:target="photo">Uploading photo...</flux:text>
                        @error('photo') <flux:text size="sm" class="text-red-600">{{ $message }}</flux:text> @enderror
                    </div>
                </div>
            </div>

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                {{-- @chisel-email-verification --}}
                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
                {{-- @end-chisel-email-verification --}}
            </div>

            <flux:input wire:model="institution" :label="__('Institution / Affiliation')" type="text" required autocomplete="organization" />

            <flux:input wire:model="country" :label="__('Country')" type="text" required autocomplete="country-name" />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ $requiresSetup ? __('Save & continue to dashboard') : __('Save changes') }}
                    </flux:button>
                </div>

            </div>
        </form>

        {{-- @chisel-email-verification --}}
        @if ($this->showDeleteUser)
        {{-- @end-chisel-email-verification --}}
            <livewire:pages::settings.delete-user-form />
        {{-- @chisel-email-verification --}}
        @endif
        {{-- @end-chisel-email-verification --}}
    </x-pages::settings.layout>
</section>
