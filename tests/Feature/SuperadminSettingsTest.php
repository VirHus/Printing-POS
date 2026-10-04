<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Store;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperadminSettingsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_must_sign_in_before_settings_or_owner_setup(): void
    {
        $this->get(route('settings.edit'))->assertRedirect(route('login'));
        $this->get(route('superadmin.setup'))->assertRedirect(route('login'));
    }

    public function test_administrator_cannot_read_or_change_system_settings(): void
    {
        $admin = $this->createUser('Administrator');

        $this->actingAs($admin)->get(route('settings.edit'))->assertForbidden();
        $this->put(route('settings.update'), $this->settingsPayload())->assertForbidden();

        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_cashier_cannot_access_settings_or_owner_setup(): void
    {
        $cashier = $this->createUser('Cashier');

        $this->actingAs($cashier)->get(route('settings.edit'))->assertForbidden();
        $this->put(route('settings.update'), $this->settingsPayload())->assertForbidden();
        $this->get(route('superadmin.setup'))->assertForbidden();
        $this->post(route('superadmin.setup.store'), [])->assertForbidden();
    }

    public function test_owner_setup_creates_separate_account_and_closes_after_setup(): void
    {
        $admin = $this->createUser('Administrator');
        $password = 'Owner-password-2026';

        $this->actingAs($admin)->get(route('superadmin.setup'))->assertSee(config('system.superadmin_email'));
        $this->post(route('superadmin.setup.store'), [
            'name' => 'Lester John',
            'password' => $password,
            'password_confirmation' => $password,
            'email' => 'ignored@example.com',
            'role' => 'Cashier',
        ])->assertRedirect(route('settings.edit'))->assertSessionHasNoErrors();

        $owner = User::where('email', config('system.superadmin_email'))->firstOrFail();
        $this->assertAuthenticatedAs($owner);
        $this->assertSame('Superadmin', $owner->role);
        $this->assertTrue(Hash::check($password, $owner->password));
        $this->assertSame('Administrator', $admin->fresh()->role);
        $this->get(route('settings.edit'))->assertSee('Superadmin control panel');
        $this->actingAs($admin)->get(route('superadmin.setup'))->assertNotFound();
        $this->post(route('superadmin.setup.store'), [
            'name' => 'Another Owner', 'password' => $password, 'password_confirmation' => $password,
        ])->assertNotFound();
        $this->assertDatabaseCount('users', 2);
    }

    public function test_owner_setup_rejects_short_or_unconfirmed_password(): void
    {
        $admin = $this->createUser('Administrator');

        $this->actingAs($admin)->post(route('superadmin.setup.store'), [
            'name' => 'Owner', 'password' => 'short', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['role' => 'Superadmin']);
    }

    public function test_superadmin_can_save_branding_and_custom_receipts(): void
    {
        $owner = $this->createUser('Superadmin');
        $payload = $this->settingsPayload();
        $sale = Sale::create([
            'user_id' => $owner->id, 'store_id' => $owner->store_id,
            'total_amount' => 20, 'cash_received' => 20, 'change_amount' => 0, 'payment_method' => 'Cash',
        ]);

        $this->actingAs($owner)->put(route('settings.update'), $payload)->assertRedirect(route('settings.edit'))->assertSessionHasNoErrors();

        $values = SystemSetting::findOrFail(1)->values;
        $this->assertSame('Lester Print Shop', $values['system_name']);
        $this->assertSame('#7c3aed', $values['accent_color']);
        $this->assertSame('compact', $values['sidebar_style']);
        $this->get(route('settings.edit'))->assertSee('Lester Print Shop')->assertSee('System Settings')->assertSee('220px');
        $this->actingAs($this->createUser('Administrator'))->get(route('dashboard'))->assertSee('Lester Print Shop')->assertSee('220px');
        $this->get(route('sales.show', $sale))->assertSee('Lester Printing')->assertSee('See you again!')->assertSee('Your purchase');
        $this->get(route('pos.index'))->assertSee('Lester Printing')->assertSee('See you again!');
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertSee('Lester Print Shop')->assertSee('Welcome to our shop.');
    }

    public function test_branding_text_is_escaped_and_invalid_theme_values_are_rejected(): void
    {
        $owner = $this->createUser('Superadmin');
        $payload = $this->settingsPayload();
        $payload['system_name'] = '<script>alert(1)</script>';

        $this->actingAs($owner)->put(route('settings.update'), $payload)->assertSessionHasNoErrors();
        $this->get(route('settings.edit'))->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $payload['accent_color'] = '</style><script>alert(1)</script>';
        $payload['sidebar_style'] = 'arbitrary-css';
        $this->put(route('settings.update'), $payload)->assertSessionHasErrors(['accent_color', 'sidebar_style']);
        $this->assertSame('#7c3aed', SystemSetting::findOrFail(1)->values['accent_color']);
    }

    public function test_uploaded_logo_is_stored_and_can_be_removed(): void
    {
        Storage::fake('public');
        $owner = $this->createUser('Superadmin');
        $payload = $this->settingsPayload();
        $payload['logo'] = $this->pngUpload();

        $this->actingAs($owner)->put(route('settings.update'), $payload)->assertSessionHasNoErrors();

        $path = SystemSetting::findOrFail(1)->values['logo_path'];
        Storage::disk('public')->assertExists($path);
        $this->get(route('settings.edit'))->assertSee('storage/'.$path, false);
        unset($payload['logo']);
        $payload['remove_logo'] = 1;
        $this->put(route('settings.update'), $payload)->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($path);
        $this->assertNull(SystemSetting::findOrFail(1)->values['logo_path']);
    }

    public function test_unsafe_or_fake_image_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $owner = $this->createUser('Superadmin');
        $payload = $this->settingsPayload();
        $payload['logo'] = UploadedFile::fake()->createWithContent('logo.png', '<?php echo "unsafe";');

        $this->actingAs($owner)->put(route('settings.update'), $payload)->assertSessionHasErrors('logo');

        Storage::disk('public')->assertDirectoryEmpty('branding');
        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_administrator_cannot_promote_themselves_or_modify_superadmin(): void
    {
        $owner = $this->createUser('Superadmin');
        $admin = $this->createUser('Administrator');
        $payload = ['name' => $admin->name, 'email' => $admin->email, 'role' => 'Superadmin', 'store_id' => $admin->store_id];

        $this->actingAs($admin)->put(route('users.update', $admin), $payload)->assertSessionHasErrors('role');
        $this->get(route('users.edit', $owner))->assertForbidden();
        $this->put(route('users.update', $owner), $payload)->assertForbidden();
        $this->patch(route('users.toggle', $owner))->assertForbidden();
        $this->delete(route('users.destroy', $owner))->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'Superadmin', 'is_active' => true]);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'Administrator']);
    }

    public function test_superadmin_cannot_remove_their_own_owner_access(): void
    {
        $owner = $this->createUser('Superadmin');

        $this->actingAs($owner)->put(route('users.update', $owner), [
            'name' => $owner->name, 'email' => $owner->email, 'role' => 'Cashier', 'store_id' => $owner->store_id,
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'Superadmin']);
    }

    public function test_superadmin_has_customization_access_but_cannot_operate_sales(): void
    {
        $owner = $this->createUser('Superadmin');
        $otherStore = Store::create(['name' => 'Remote Branch']);
        $sale = Sale::create([
            'user_id' => $owner->id, 'store_id' => $otherStore->id,
            'total_amount' => 33, 'cash_received' => 33, 'change_amount' => 0, 'payment_method' => 'Cash',
        ]);

        $this->actingAs($owner)->get(route('sales.show', $sale))->assertForbidden();
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('pos.index'))->assertForbidden();
        $this->post(route('pos.store'), [])->assertForbidden();
        $this->get(route('sales.index'))->assertForbidden();
        $this->get(route('reports.index', ['store_id' => $otherStore->id]))->assertForbidden();
        $this->get(route('users.index'))->assertOk()->assertDontSee('href="'.route('pos.index').'"', false);
        $this->get(route('products.index'))->assertForbidden();
        $this->get(route('stores.index'))->assertOk();
        $this->get(route('settings.edit'))->assertOk()
            ->assertDontSee('href="'.route('dashboard').'"', false)
            ->assertDontSee('href="'.route('pos.index').'"', false)
            ->assertDontSee('href="'.route('sales.index').'"', false);
        $this->get('/')->assertRedirect(route('settings.edit'));
        $this->get(route('login'))->assertRedirect(route('settings.edit'));
    }

    public function test_superadmin_login_ignores_an_operational_intended_destination(): void
    {
        $owner = $this->createUser('Superadmin');
        $owner->update(['password' => 'Owner-password-123']);

        $this->withSession(['url.intended' => route('pos.index')])
            ->post(route('login.process'), ['email' => $owner->email, 'password' => 'Owner-password-123'])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionMissing('url.intended');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_store_photo_is_editable_only_by_superadmin(): void
    {
        Storage::fake('public');
        $owner = $this->createUser('Superadmin');
        $admin = $this->createUser('Administrator');
        $store = $owner->store;
        $payload = ['name' => $store->name, 'address' => '', 'is_active' => 1, 'image' => $this->pngUpload()];

        $this->actingAs($admin)->put(route('stores.update', $store), $payload)->assertForbidden();
        $this->actingAs($owner)->put(route('stores.update', $store), $payload)->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists($store->fresh()->image_path);
    }

    private function createUser(string $role): User
    {
        $store = Store::create(['name' => $role.' Store']);

        return User::factory()->create(['role' => $role, 'store_id' => $store->id, 'is_active' => true]);
    }

    /** @return array<string, string> */
    private function settingsPayload(): array
    {
        return [
            'system_name' => 'Lester Print Shop', 'tagline' => 'Your print partner',
            'header_text' => 'Printing and services', 'login_description' => 'Welcome to our shop.',
            'business_name' => 'Lester Printing', 'business_address' => 'Main Street',
            'receipt_heading' => 'Your purchase', 'receipt_footer' => 'See you again!',
            'accent_color' => '#7c3aed', 'sidebar_style' => 'compact', 'content_width' => 'full',
        ];
    }

    private function pngUpload(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jX1cAAAAASUVORK5CYII='));
    }
}
