<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_one_verified_account_for_each_core_role(): void
    {
        $this->seed(DemoAccountsSeeder::class);

        $this->assertDatabaseCount('users', 4);

        foreach (UserRole::cases() as $role) {
            $user = User::query()->where('email', $role->value.'@iches.id')->firstOrFail();

            $this->assertSame($role, $user->role);
            $this->assertTrue(Hash::check('password', $user->password));
            $this->assertNotNull($user->email_verified_at);
            $this->assertSame('Universitas Islam Syarifuddin', $user->institution);
            $this->assertSame('Indonesia', $user->country);
        }
    }

    public function test_reseeding_is_idempotent_and_preserves_existing_user_changes(): void
    {
        $this->seed(DemoAccountsSeeder::class);

        $reviewer = User::query()->where('email', 'reviewer@iches.id')->firstOrFail();
        $reviewer->name = 'Edited Reviewer';
        $reviewer->save();
        $originalHash = $reviewer->password;

        $this->seed(DemoAccountsSeeder::class);

        $this->assertDatabaseCount('users', 4);
        $this->assertSame('Edited Reviewer', $reviewer->refresh()->name);
        $this->assertSame($originalHash, $reviewer->password);
    }

    public function test_existing_non_admin_account_cannot_be_promoted_by_demo_seeder(): void
    {
        User::factory()->create(['email' => 'admin@iches.id']);

        $this->expectException(\RuntimeException::class);

        $this->seed(DemoAccountsSeeder::class);
    }
}
