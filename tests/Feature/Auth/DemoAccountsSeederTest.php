<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_one_verified_account_for_every_role(): void
    {
        $this->seed(DemoAccountsSeeder::class);

        $this->assertDatabaseCount('users', 5);

        foreach (UserRole::cases() as $role) {
            $user = User::query()->where('email', $role->value.'@iches.id')->firstOrFail();

            $this->assertSame($role, $user->role);
            $this->assertSame($role !== UserRole::Superadmin, Hash::check('password', $user->password));
            $this->assertTrue($user->hasRole($role));
            if ($role === UserRole::Superadmin) {
                $this->assertCount(count(UserRole::cases()), $user->assignedRoles());
            }
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
        $root = User::query()->where('email', 'superadmin@iches.id')->firstOrFail();
        $originalRootHash = $root->password;

        $this->seed(DemoAccountsSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertSame('Edited Reviewer', $reviewer->refresh()->name);
        $this->assertSame($originalHash, $reviewer->password);
        $this->assertSame($originalRootHash, $root->refresh()->password);
        $this->assertDatabaseCount('user_roles', 9);
    }

    public function test_default_database_seeder_backfills_all_role_memberships_without_model_events(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('user_roles', 9);
        $root = User::query()->where('email', 'superadmin@iches.id')->firstOrFail();
        $this->assertCount(5, $root->assignedRoles());
        $this->assertTrue($root->hasRole(UserRole::Superadmin));
    }

    public function test_existing_non_admin_account_cannot_be_promoted_by_demo_seeder(): void
    {
        User::factory()->create(['email' => 'admin@iches.id']);

        $this->expectException(\RuntimeException::class);

        $this->seed(DemoAccountsSeeder::class);
    }
}
