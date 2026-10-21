<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SystemBuilderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_access_builder_or_custom_pages(): void
    {
        $this->get(route('settings.builder.edit'))->assertRedirect(route('login'));
        $this->put(route('settings.builder.update'), [])->assertRedirect(route('login'));
        $this->get(route('settings.pages.index'))->assertRedirect(route('login'));
        $this->get(route('pages.show', 'staff-guide'))->assertRedirect(route('login'));
    }

    #[DataProvider('operationalRoles')]
    public function test_operational_roles_cannot_change_builder_or_pages(string $role): void
    {
        $this->actingAs($this->user($role))->get(route('settings.builder.edit'))->assertForbidden();
        $this->put(route('settings.builder.update'), $this->payload())->assertForbidden();
        $this->delete(route('settings.builder.destroy'))->assertForbidden();
        $this->get(route('settings.pages.index'))->assertForbidden();
        $this->post(route('settings.pages.store'), $this->pagePayload())->assertForbidden();
        $this->assertDatabaseCount('system_settings', 0);
    }

    public static function operationalRoles(): array
    {
        return [['Administrator'], ['Cashier']];
    }

    public function test_superadmin_can_save_appearance_and_reorder_hide_resize_dashboard_cards(): void
    {
        SystemSetting::create(['id' => 1, 'values' => ['system_name' => 'Keep branding', 'sidebar_items' => SystemSetting::defaultSidebar()]]);
        $payload = $this->payload();
        $payload['widgets'] = array_reverse($payload['widgets']);
        $payload['widgets'][0]['columns'] = 6;
        $payload['widgets'][0]['title'] = 'Latest purchases';
        $payload['widgets'][1]['cashier'] = false;
        $payload['widgets'][1]['admin'] = false;
        $this->actingAs($this->user('Superadmin'))->get(route('settings.builder.edit'))->assertOk()->assertSee('Dashboard builder');
        $this->put(route('settings.builder.update'), $payload)->assertRedirect(route('settings.builder.edit'))->assertSessionHasNoErrors();
        $values = SystemSetting::findOrFail(1)->values;
        $this->assertSame('Keep branding', $values['system_name']);
        $this->assertSame('solid', $values['theme']);
        $this->assertSame('serif', $values['font_family']);
        $this->assertSame(6, $values['dashboard_widgets'][0]['columns']);
        $this->actingAs($this->user('Administrator'))->get(route('dashboard'))->assertOk()
            ->assertSee('Custom workspace')->assertSee('font-family: Georgia, serif;', false)
            ->assertSee('Latest purchases')
            ->assertSeeInOrder(['data-widget="recent"', 'data-widget="overview"', 'data-widget="services"'])
            ->assertDontSee('data-widget="actions"', false);
        $this->actingAs($this->user('Cashier'))->get(route('dashboard'))->assertOk()->assertDontSee('data-widget="actions"', false);
        $this->actingAs($this->user('Superadmin'))->put(route('settings.update'), SystemSetting::DEFAULTS)->assertSessionHasNoErrors();
        $this->assertSame('solid', SystemSetting::findOrFail(1)->values['theme']);
    }

    public function test_disabled_modules_are_hidden_and_block_direct_get_and_post_requests(): void
    {
        $payload = $this->payload();
        $payload['modules']['pos'] = ['admin' => false, 'cashier' => false];
        $payload['modules']['products']['cashier'] = true;
        $this->actingAs($this->user('Superadmin'))->put(route('settings.builder.update'), $payload)->assertSessionHasNoErrors();
        $this->assertFalse(SystemSetting::findOrFail(1)->values['modules']['products']['cashier']);
        foreach (['Administrator', 'Cashier'] as $role) {
            $this->actingAs($this->user($role))->get(route('dashboard'))->assertOk()->assertDontSee('href="'.route('pos.index').'"', false);
            $this->get(route('pos.index'))->assertForbidden();
            $this->post(route('pos.store'), [])->assertForbidden();
            $this->get(route('profile.edit'))->assertOk();
        }
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_all_disabled_modules_fall_back_to_profile_and_owner_can_restore_defaults(): void
    {
        $payload = $this->payload();
        foreach ($payload['modules'] as &$module) {
            $module = ['admin' => false, 'cashier' => false];
        }
        unset($module);
        $owner = $this->user('Superadmin');
        $this->actingAs($owner)->put(route('settings.builder.update'), $payload)->assertSessionHasNoErrors();
        $admin = $this->user('Administrator');
        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('profile.edit'));
        $this->get(route('profile.edit'))->assertOk();
        $this->actingAs($owner)->get(route('settings.builder.edit'))->assertOk();
        $this->get(route('users.index'))->assertOk();
        $this->delete(route('settings.builder.destroy'))->assertRedirect(route('settings.builder.edit'));
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Business Dashboard');
    }

    public function test_unsafe_appearance_invalid_widgets_and_partial_module_settings_are_rejected(): void
    {
        $payload = $this->payload();
        $payload['background_color'] = '</style><script>alert(1)</script>';
        $payload['font_family'] = 'url(unsafe)';
        $payload['widgets'][0]['key'] = '../../auth/login';
        $payload['widgets'][1]['columns'] = 500;
        $payload['modules']['pos']['extra'] = true;
        $this->actingAs($this->user('Superadmin'))->put(route('settings.builder.update'), $payload)
            ->assertSessionHasErrors(['background_color', 'font_family', 'widgets.0.key', 'widgets.1.columns', 'modules.pos']);
        $this->assertDatabaseCount('system_settings', 0);
        $payload = $this->payload();
        unset($payload['modules']['pos']);
        $this->put(route('settings.builder.update'), $payload)->assertSessionHasErrors('modules');
        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_custom_pages_are_escaped_scoped_by_role_and_can_be_disabled_without_losing_content(): void
    {
        $owner = $this->user('Superadmin');
        $payload = $this->pagePayload();
        $payload['body'] = '<script>alert(1)</script>';
        $payload['cashier'] = false;
        $this->actingAs($owner)->post(route('settings.pages.store'), $payload)->assertRedirect(route('settings.pages.index'))->assertSessionHasNoErrors();
        $this->get(route('settings.pages.index'))->assertOk()->assertSee('Staff guide');
        $this->assertSame('<script>alert(1)</script>', SystemSetting::findOrFail(1)->values['custom_pages'][0]['body']);
        $admin = $this->user('Administrator');
        $this->actingAs($admin)->get(route('dashboard'))->assertSee('href="'.route('pages.show', 'staff-guide').'"', false);
        $this->get(route('pages.show', 'staff-guide'))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->actingAs($this->user('Cashier'))->get(route('pages.show', 'staff-guide'))->assertNotFound();
        $this->get(route('dashboard'))->assertDontSee('href="'.route('pages.show', 'staff-guide').'"', false);
        $payload['enabled'] = false;
        $this->actingAs($owner)->put(route('settings.pages.update', 'staff-guide'), $payload)->assertSessionHasNoErrors();
        $this->delete(route('settings.builder.destroy'))->assertSessionHasNoErrors();
        $this->actingAs($admin)->get(route('pages.show', 'staff-guide'))->assertNotFound();
        $this->assertSame('<script>alert(1)</script>', SystemSetting::findOrFail(1)->values['custom_pages'][0]['body']);
    }

    public function test_pos_requires_transaction_access_for_receipts(): void
    {
        $payload = $this->payload();
        $payload['modules']['sales']['cashier'] = false;
        $this->actingAs($this->user('Superadmin'))->put(route('settings.builder.update'), $payload)
            ->assertSessionHasErrors('modules.pos.cashier');
        $this->assertDatabaseCount('system_settings', 0);
        $payload['modules']['pos']['cashier'] = false;
        $this->put(route('settings.builder.update'), $payload)->assertSessionHasNoErrors();
        $this->actingAs($this->user('Cashier'))->get(route('dashboard'))->assertOk()->assertDontSee('data-widget="recent"', false);
        $this->get(route('sales.index'))->assertForbidden();
    }

    public function test_custom_pages_require_unique_safe_addresses_and_valid_content(): void
    {
        $this->actingAs($this->user('Superadmin'))->post(route('settings.pages.store'), [])->assertSessionHasErrors(['title', 'slug', 'body']);
        $this->post(route('settings.pages.store'), $this->pagePayload())->assertSessionHasNoErrors();
        $this->post(route('settings.pages.store'), $this->pagePayload())->assertSessionHasErrors('slug');
        $payload = $this->pagePayload();
        $payload['slug'] = '../settings';
        $this->post(route('settings.pages.store'), $payload)->assertSessionHasErrors('slug');
        $this->put(route('settings.pages.update', 'missing'), $this->pagePayload())->assertNotFound();
        $this->assertCount(1, SystemSetting::findOrFail(1)->values['custom_pages']);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true, 'store_id' => Store::create(['name' => $role.' Branch'])->id]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        $modules = [];
        foreach (SystemSetting::MODULES as $key => $module) {
            $modules[$key] = ['admin' => true, 'cashier' => $module['cashier']];
        }

        return array_replace(SystemSetting::APPEARANCE_DEFAULTS, [
            'theme' => 'solid', 'font_family' => 'serif', 'font_size' => 'large', 'spacing' => 'compact',
            'corner_style' => 'square', 'background_color' => '#182030', 'dashboard_title' => 'Custom workspace',
            'modules' => $modules, 'widgets' => SystemSetting::current()->dashboardWidgets(),
        ]);
    }

    /** @return array<string, mixed> */
    private function pagePayload(): array
    {
        return ['title' => 'Staff guide', 'slug' => 'staff-guide', 'section' => 'Information', 'body' => 'Instructions for staff.', 'admin' => true, 'cashier' => true, 'enabled' => true];
    }
}
