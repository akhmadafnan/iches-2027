<?php

namespace Tests\Feature\Settings;

use App\Enums\RegistrationIntention;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_is_stored_in_lowercase_so_the_user_can_still_log_in(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'Test@Example.com')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertEquals('test@example.com', $user->refresh()->email);

        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'email' => 'Test@Example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_must_be_unique_regardless_of_case(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'Taken@Example.com')
            ->call('updateProfileInformation')
            ->assertHasErrors(['email']);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_institution_and_country_can_be_updated_without_affecting_another_user(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $originalOtherInstitution = $anotherUser->institution;
        $originalOtherCountry = $anotherUser->country;

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->assertSet('institution', $user->institution)
            ->assertSet('country', $user->country)
            ->set('institution', 'Universitas Islam Syarifuddin')
            ->set('country', 'Indonesia')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertSame('Universitas Islam Syarifuddin', $user->refresh()->institution);
        $this->assertSame('Indonesia', $user->country);
        $this->assertSame($originalOtherInstitution, $anotherUser->refresh()->institution);
        $this->assertSame($originalOtherCountry, $anotherUser->country);
    }

    public function test_profile_rejects_invalid_institution_or_country(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('institution', '')
            ->set('country', str_repeat('x', 101))
            ->call('updateProfileInformation')
            ->assertHasErrors(['institution' => 'required', 'country' => 'max']);

        $this->assertSame($user->getRawOriginal('institution'), $user->refresh()->institution);
    }

    public function test_profile_changes_never_modify_account_role_or_registration_intention(): void
    {
        $user = User::factory()->create([
            'registration_intention' => RegistrationIntention::SubmitPaper,
        ]);
        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('name', 'Updated Participant')
            ->set('institution', 'Updated Affiliation')
            ->set('country', 'Singapore')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertSame(UserRole::User, $user->refresh()->role);
        $this->assertSame(RegistrationIntention::SubmitPaper, $user->registration_intention);
    }

    public function test_profile_is_not_accessible_to_guest(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.delete-user-modal')
            ->set('password', 'password')
            ->call('deleteUser');

        $response
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertFalse(auth()->check());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.delete-user-modal')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $response->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }
}
