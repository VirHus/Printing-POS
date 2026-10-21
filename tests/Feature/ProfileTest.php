<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_view_or_update_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->put(route('profile.update'), [])->assertRedirect(route('login'));
        $this->put(route('profile.password.update'), [])->assertRedirect(route('login'));
    }

    #[DataProvider('roles')]
    public function test_each_role_can_edit_only_their_personal_details(string $role): void
    {
        $user = User::factory()->create(['role' => $role, 'is_active' => true]);
        $other = User::factory()->create();
        $this->actingAs($user)->get(route('profile.edit'))->assertSee('Personal details')->assertSee('Change password')->assertSee('href="'.route('profile.edit').'"', false);

        $this->put(route('profile.update'), ['name' => 'Updated Name', 'email' => $user->email, 'role' => 'Superadmin', 'store_id' => 99, 'is_active' => false, 'id' => $other->id])
            ->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame($role, $user->fresh()->role);
        $this->assertSame($user->store_id, $user->fresh()->store_id);
        $this->assertTrue($user->fresh()->is_active);
        $this->assertSame($other->name, $other->fresh()->name);
    }

    public static function roles(): array
    {
        return [['Administrator'], ['Cashier'], ['Superadmin']];
    }

    public function test_email_change_requires_current_password_and_resets_verification(): void
    {
        $user = User::factory()->create(['role' => 'Cashier', 'is_active' => true]);
        $payload = ['name' => $user->name, 'email' => 'new@example.com'];

        $this->actingAs($user)->put(route('profile.update'), $payload)->assertSessionHasErrors('current_password');
        $this->assertSame($user->email, $user->fresh()->email);
        $this->put(route('profile.update'), $payload + ['current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertSame('new@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'Administrator', 'is_active' => true]);
        $other = User::factory()->create();
        $this->actingAs($user)->put(route('profile.update'), ['name' => $user->name, 'email' => $other->email, 'current_password' => 'password'])->assertSessionHasErrors('email');
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_password_change_requires_correct_current_password_and_confirmation(): void
    {
        $user = User::factory()->create(['role' => 'Cashier', 'is_active' => true]);
        $payload = ['current_password' => 'wrong', 'password' => 'New-password-123', 'password_confirmation' => 'New-password-123'];
        $this->actingAs($user)->put(route('profile.password.update'), $payload)->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $payload['current_password'] = 'password';
        $payload['password_confirmation'] = 'different';
        $this->put(route('profile.password.update'), $payload)->assertSessionHasErrors('password');
        $payload['password_confirmation'] = 'New-password-123';
        $this->put(route('profile.password.update'), $payload)->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('New-password-123', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_account_cannot_edit_profile(): void
    {
        $user = User::factory()->create(['role' => 'Cashier', 'is_active' => false]);
        $this->actingAs($user)->put(route('profile.update'), ['name' => 'Changed', 'email' => $user->email])->assertRedirect(route('login'));
        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_profile_name_is_escaped(): void
    {
        $user = User::factory()->create(['name' => '<script>alert(1)</script>', 'role' => 'Superadmin', 'is_active' => true]);
        $this->actingAs($user)->get(route('profile.edit'))->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }
}
