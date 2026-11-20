<?php

namespace Tests\Feature\Auth;

use App\Enums\RegistrationIntention;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="institution"', false)
            ->assertSee('name="country"', false)
            ->assertSee('name="registration_intention"', false);
    }

    public function test_new_users_can_register_as_an_ordinary_participant(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), $this->registrationPayload());

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertSame('Universitas Islam Syarifuddin', $user->institution);
        $this->assertSame('Indonesia', $user->country);
        $this->assertSame(RegistrationIntention::SubmitPaper, $user->registration_intention);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_missing_institution_country_or_intention(): void
    {
        $payload = $this->registrationPayload();
        unset($payload['institution'], $payload['country'], $payload['registration_intention']);

        $this->post(route('register.store'), $payload)
            ->assertSessionHasErrors(['institution', 'country', 'registration_intention']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_invalid_intention(): void
    {
        $payload = $this->registrationPayload();
        $payload['registration_intention'] = 'admin';

        $this->post(route('register.store'), $payload)
            ->assertSessionHasErrors('registration_intention');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_role_fields_submitted_by_users_are_ignored(): void
    {
        $payload = $this->registrationPayload();
        $payload['role'] = 'admin';
        $payload['is_admin'] = true;

        $this->post(route('register.store'), $payload)
            ->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertSame(UserRole::User, $user->role);
        $this->assertNotSame(true, $user->getAttribute('is_admin'));
        $this->assertSame(RegistrationIntention::SubmitPaper, $user->registration_intention);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->post(route('register.store'), $this->registrationPayload())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    /**
     * @return array<string, string>
     */
    private function registrationPayload(): array
    {
        return [
            'name' => 'ICHES Test Participant',
            'email' => 'test@example.com',
            'institution' => 'Universitas Islam Syarifuddin',
            'country' => 'Indonesia',
            'registration_intention' => RegistrationIntention::SubmitPaper->value,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }
}
