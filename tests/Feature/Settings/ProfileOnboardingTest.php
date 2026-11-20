<?php

namespace Tests\Feature\Settings;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_passkeys_are_disabled_across_authentication(): void
    {
        $this->assertFalse(Features::canManagePasskeys());
        $this->get('/.well-known/passkey-endpoints')->assertNotFound();
        $this->get(route('login'))->assertOk()->assertDontSee('Sign in with a passkey');
    }

    public function test_registered_users_are_redirected_to_profile_until_photo_is_uploaded(): void
    {
        $user = User::factory()->create(['profile_setup_required' => true]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'));

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Complete your ICHES profile')
            ->assertSee('Choose profile photo');
    }

    public function test_admin_cannot_bypass_profile_setup_to_admin_area(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'profile_setup_required' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_existing_users_without_photo_remain_unblocked(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_photo_is_required_during_first_profile_setup(): void
    {
        $user = User::factory()->create(['profile_setup_required' => true]);
        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->call('updateProfileInformation')
            ->assertHasErrors(['photo' => 'required']);

        $this->assertTrue($user->fresh()->profile_setup_required);
    }

    public function test_uploading_photo_completes_setup_and_stores_photo_privately(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['profile_setup_required' => true]);
        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('photo', UploadedFile::fake()->image('photo.jpg', 500, 500))
            ->call('updateProfileInformation')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $user->refresh();

        $this->assertFalse($user->profile_setup_required);
        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringStartsWith('profile-photos/'.$user->id.'/', $user->profile_photo_path);
        Storage::disk('local')->assertExists($user->profile_photo_path);

        $this->get(route('profile.photo'))->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get(route('dashboard'))->assertOk();
        $this->post(route('logout'));
        $this->get(route('profile.photo'))->assertRedirect(route('login'));
    }

    public function test_other_users_cannot_access_a_private_photo(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create([
            'profile_photo_path' => 'profile-photos/1/private.jpg',
        ]);
        Storage::disk('local')->put($owner->profile_photo_path, 'test image');

        $this->actingAs(User::factory()->create())
            ->get(route('profile.photo'))
            ->assertNotFound();
    }

    public function test_invalid_photo_is_rejected_without_finishing_onboarding(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['profile_setup_required' => true]);
        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('photo', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->call('updateProfileInformation')
            ->assertHasErrors('photo');

        $this->assertTrue($user->fresh()->profile_setup_required);
        $this->assertNull($user->fresh()->profile_photo_path);
    }

    public function test_replacing_a_photo_removes_the_old_private_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $oldPath = 'profile-photos/'.$user->id.'/old.jpg';

        Storage::disk('local')->put($oldPath, 'old image');
        $user->forceFill(['profile_photo_path' => $oldPath])->save();
        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('photo', UploadedFile::fake()->image('new.jpg', 500, 500))
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertNotSame($oldPath, $user->refresh()->profile_photo_path);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($user->profile_photo_path);
    }
}
