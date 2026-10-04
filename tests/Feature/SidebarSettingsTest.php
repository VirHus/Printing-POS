<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SidebarSettingsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_open_or_change_sidebar(): void
    {
        $this->get(route('settings.sidebar.edit'))->assertRedirect(route('login'));
        $this->put(route('settings.sidebar.update'), [])->assertRedirect(route('login'));
        $this->delete(route('settings.sidebar.destroy'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('system_settings', 0);
    }

    #[DataProvider('operationalRoles')]
    public function test_operational_roles_cannot_customize_sidebar(string $role): void
    {
        $this->actingAs($this->user($role))->get(route('settings.sidebar.edit'))->assertForbidden();
        $this->put(route('settings.sidebar.update'), [])->assertForbidden();
        $this->delete(route('settings.sidebar.destroy'))->assertForbidden();
        $this->assertDatabaseCount('system_settings', 0);
    }

    public static function operationalRoles(): array
    {
        return ['admin' => ['Administrator'], 'cashier' => ['Cashier']];
    }

    public function test_owner_can_save_order_sections_and_role_visibility_without_overwriting_branding(): void
    {
        SystemSetting::create(['id' => 1, 'values' => ['system_name' => 'Custom Shop']]);
        $owner = $this->user('Superadmin');
        $items = [
            $this->item(['label' => 'Start selling', 'destination' => 'pos.index', 'icon' => 'cart', 'section' => 'Sales desk', 'cashier' => true]),
            $this->item(['label' => 'Staff accounts', 'destination' => 'users.index', 'cashier' => true]),
            $this->item(['label' => 'Cashier handbook', 'destination' => 'external', 'url' => 'https://example.com/handbook', 'admin' => false, 'cashier' => true]),
            $this->item(['label' => 'Hidden menu', 'admin' => false, 'cashier' => false]),
        ];

        $this->actingAs($owner)->put(route('settings.sidebar.update'), ['items' => $items])->assertRedirect(route('settings.sidebar.edit'))->assertSessionHasNoErrors();

        $this->assertSame($items, SystemSetting::findOrFail(1)->values['sidebar_items']);
        $this->assertSame('Custom Shop', SystemSetting::findOrFail(1)->values['system_name']);
        $this->get(route('settings.sidebar.edit'))->assertSee('Start selling')->assertSee('Add button')->assertSee('Restore defaults');
        $this->actingAs($this->user('Administrator'))->get(route('dashboard'))
            ->assertSeeInOrder(['Sales desk', 'Start selling', 'Staff accounts'])
            ->assertDontSee('Cashier handbook')->assertDontSee('Hidden menu');
        $this->actingAs($this->user('Cashier'))->get(route('dashboard'))
            ->assertSee('Start selling')->assertSee('Cashier handbook')->assertSee('https://example.com/handbook')
            ->assertDontSee('Staff accounts')->assertDontSee('Hidden menu');
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('settings.sidebar.edit'))->assertForbidden();
    }

    public function test_sidebar_text_is_escaped(): void
    {
        $owner = $this->user('Superadmin');
        $item = $this->item(['label' => '<script>alert(1)</script>', 'section' => '<b>Section</b>']);

        $this->actingAs($owner)->put(route('settings.sidebar.update'), ['items' => [$item]])->assertSessionHasNoErrors();

        $this->actingAs($this->user('Administrator'))->get(route('dashboard'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;b&gt;Section&lt;/b&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    #[DataProvider('invalidItems')]
    public function test_invalid_menu_buttons_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->user('Superadmin'))
            ->put(route('settings.sidebar.update'), ['items' => [$this->item($overrides)]])
            ->assertSessionHasErrors('items.0'.($field === '' ? '' : '.'.$field));

        $this->assertDatabaseCount('system_settings', 0);
    }

    public static function invalidItems(): array
    {
        return [
            'script link' => [['destination' => 'external', 'url' => 'javascript:alert(1)'], 'url'],
            'external needs URL' => [['destination' => 'external', 'url' => ''], 'url'],
            'internal page whitelist' => [['destination' => 'settings.edit'], 'destination'],
            'icon whitelist' => [['icon' => '<svg onload="alert(1)">'], 'icon'],
            'empty label' => [['label' => ''], 'label'],
            'long label' => [['label' => str_repeat('a', 61)], 'label'],
            'long section' => [['section' => str_repeat('a', 61)], 'section'],
            'invalid visibility' => [['admin' => 'yes'], 'admin'],
            'unexpected nested key' => [['role' => 'Superadmin'], ''],
        ];
    }

    public function test_empty_and_oversized_menus_cannot_be_saved(): void
    {
        $this->actingAs($this->user('Superadmin'))->put(route('settings.sidebar.update'), ['items' => []])->assertSessionHasErrors('items');
        $this->put(route('settings.sidebar.update'), ['items' => array_fill(0, 31, $this->item())])->assertSessionHasErrors('items');
        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_restore_defaults_preserves_branding_and_restores_original_buttons(): void
    {
        SystemSetting::create(['id' => 1, 'values' => ['system_name' => 'Keep this name', 'sidebar_items' => [$this->item(['label' => 'Custom button'])]]]);

        $this->actingAs($this->user('Superadmin'))->delete(route('settings.sidebar.destroy'))->assertRedirect(route('settings.sidebar.edit'));

        $this->assertSame(['system_name' => 'Keep this name'], SystemSetting::findOrFail(1)->values);
        $this->actingAs($this->user('Administrator'))->get(route('dashboard'))->assertSee('Point of Sale')->assertSee('Transactions')->assertDontSee('Custom button');
    }

    public function test_saving_branding_preserves_custom_sidebar(): void
    {
        $items = [$this->item(['label' => 'Keep my sidebar'])];
        SystemSetting::create(['id' => 1, 'values' => ['sidebar_items' => $items]]);

        $this->actingAs($this->user('Superadmin'))->put(route('settings.update'), SystemSetting::DEFAULTS)
            ->assertRedirect(route('settings.edit'))->assertSessionHasNoErrors();

        $this->assertSame($items, SystemSetting::findOrFail(1)->values['sidebar_items']);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true, 'store_id' => Store::create(['name' => $role.' Store'])->id]);
    }

    private function item(array $overrides = []): array
    {
        return array_replace(['label' => 'Home', 'section' => 'Main', 'destination' => 'dashboard', 'url' => '', 'icon' => 'grid', 'admin' => true, 'cashier' => false], $overrides);
    }
}
