<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    public const MODULES = [
        'dashboard' => ['label' => 'Dashboard', 'route' => 'dashboard', 'cashier' => true],
        'pos' => ['label' => 'Point of Sale', 'route' => 'pos.index', 'cashier' => true],
        'sales' => ['label' => 'Transactions', 'route' => 'sales.index', 'cashier' => true],
        'inventory' => ['label' => 'Inventory', 'route' => 'inventory.index', 'cashier' => true],
        'reports' => ['label' => 'Reports', 'route' => 'reports.index', 'cashier' => true],
        'products' => ['label' => 'Services & categories', 'route' => 'products.index', 'cashier' => false],
        'profit' => ['label' => 'Profit & expenses', 'route' => 'profit.index', 'cashier' => false],
        'stores' => ['label' => 'Stores', 'route' => 'stores.index', 'cashier' => false],
        'users' => ['label' => 'User management', 'route' => 'users.index', 'cashier' => false],
    ];

    public const DASHBOARD_WIDGETS = [
        'shortcuts' => ['label' => 'Inventory & profit links', 'columns' => 12],
        'sales' => ['label' => 'Sales total & trend', 'columns' => 6],
        'transactions' => ['label' => 'Transaction count', 'columns' => 3],
        'services' => ['label' => 'Active services', 'columns' => 3],
        'overview' => ['label' => 'Sales overview chart', 'columns' => 8],
        'actions' => ['label' => 'Quick actions', 'columns' => 4],
        'recent' => ['label' => 'Recent transactions', 'columns' => 12],
    ];

    public const APPEARANCE_DEFAULTS = [
        'theme' => 'glass', 'font_family' => 'instrument', 'font_size' => 'standard',
        'spacing' => 'comfortable', 'corner_style' => 'rounded', 'background_color' => '#0b1626',
        'dashboard_title' => 'Business Dashboard',
    ];

    public static function moduleForRoute(string $route): ?string
    {
        $prefix = explode('.', $route)[0];

        return match ($prefix) {
            'categories' => 'products',
            'expenses' => 'profit',
            default => array_key_exists($prefix, self::MODULES) ? $prefix : null,
        };
    }

    public function moduleEnabled(string $module, User $user): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }
        if (! isset(self::MODULES[$module])) {
            return false;
        }
        $role = $user->isAdministrator() ? 'admin' : 'cashier';
        if ($role === 'cashier' && ! self::MODULES[$module]['cashier']) {
            return false;
        }

        return (bool) ($this->values['modules'][$module][$role] ?? true);
    }

    public function homeRoute(User $user): string
    {
        if ($user->isSuperadmin()) {
            return 'settings.edit';
        }
        foreach (self::MODULES as $module => $definition) {
            if ($this->moduleEnabled($module, $user)) {
                return $definition['route'];
            }
        }

        return 'profile.edit';
    }

    /** @return list<array<string, mixed>> */
    public function dashboardWidgets(?User $user = null): array
    {
        $widgets = $this->values['dashboard_widgets'] ?? array_map(
            fn (string $key): array => ['key' => $key, 'columns' => self::DASHBOARD_WIDGETS[$key]['columns'], 'admin' => true, 'cashier' => true],
            array_keys(self::DASHBOARD_WIDGETS)
        );

        return $user === null ? $widgets : array_values(array_filter($widgets,
            fn (array $widget): bool => (bool) $widget[$user->isAdministrator() ? 'admin' : 'cashier']
                && ($widget['key'] !== 'recent' || $this->moduleEnabled('sales', $user))));
    }

    /** @return list<array<string, mixed>> */
    public function customPages(?User $user = null): array
    {
        $pages = $this->values['custom_pages'] ?? [];

        return $user === null || $user->isSuperadmin() ? $pages : array_values(array_filter($pages,
            fn (array $page): bool => $page['enabled'] && $page[$user->isAdministrator() ? 'admin' : 'cashier']));
    }

    public const SIDEBAR_DESTINATIONS = [
        'dashboard' => 'Dashboard', 'pos.index' => 'Point of Sale', 'sales.index' => 'Transactions',
        'stores.index' => 'Stores', 'products.index' => 'Services', 'categories.index' => 'Service Categories', 'users.index' => 'Users',
        'reports.index' => 'Reports', 'inventory.index' => 'Inventory', 'profit.index' => 'Profit & Expenses', 'external' => 'Web address',
    ];

    public const SIDEBAR_ICONS = [
        'grid' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        'cart' => 'M3 3h2l3 13h10l3-9H6M10 21h.01M18 21h.01',
        'receipt' => 'M6 2h12v20l-3-2-3 2-3-2-3 2zM9 7h6M9 11h6M9 15h3',
        'store' => 'M3 10h18l-2-7H5zM5 10v11h14V10M9 21v-7h6v7',
        'printer' => 'M6 9V2h12v7M6 18H4v-9h16v9h-2M6 14h12v8H6z',
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-4',
        'chart' => 'M4 19V9M10 19V5M16 19v-7M22 19V2M2 19h21',
        'link' => 'M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-2 2M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l2-2',
    ];

    /** @return list<array{label:string,section:string,destination:string,url:string,icon:string,admin:bool,cashier:bool}> */
    public static function defaultSidebar(): array
    {
        $items = [];
        foreach (['dashboard' => 'grid', 'pos.index' => 'cart', 'sales.index' => 'receipt', 'stores.index' => 'store', 'products.index' => 'printer', 'users.index' => 'users', 'reports.index' => 'chart', 'inventory.index' => 'store', 'profit.index' => 'chart'] as $destination => $icon) {
            $items[] = ['label' => self::SIDEBAR_DESTINATIONS[$destination], 'section' => in_array($destination, ['dashboard', 'pos.index', 'sales.index'], true) ? 'Main' : 'Management', 'destination' => $destination, 'url' => '', 'icon' => $icon, 'admin' => true, 'cashier' => in_array($destination, ['dashboard', 'pos.index', 'sales.index', 'reports.index', 'inventory.index'], true)];
        }

        return $items;
    }

    /** @return list<array<string, mixed>> */
    public function sidebarItems(?User $user = null): array
    {
        $items = $this->values['sidebar_items'] ?? self::defaultSidebar();
        if ($user === null) {
            return $items;
        }
        if ($user->isSuperadmin()) {
            return [];
        }

        return array_values(array_filter($items, function (array $item) use ($user): bool {
            $visible = $item[$user->role === 'Administrator' ? 'admin' : 'cashier'];
            $restricted = in_array($item['destination'], ['stores.index', 'products.index', 'users.index', 'profit.index'], true);

            $module = self::moduleForRoute($item['destination']);

            return $visible && (! $restricted || $user->role === 'Administrator')
                && ($module === null || $this->moduleEnabled($module, $user));
        }));
    }

    public const DEFAULTS = [
        'system_name' => 'Printing POS',
        'tagline' => 'Management System',
        'header_text' => 'Printing Services Point of Sale',
        'login_description' => 'A simple and modern point-of-sale management system for printing, photocopy, scanning, and rush ID services.',
        'business_name' => 'Printing POS',
        'business_address' => '',
        'receipt_heading' => 'Sales Receipt',
        'receipt_footer' => 'Thank you for your transaction!',
        'accent_color' => '#0d9488',
        'sidebar_style' => 'standard',
        'content_width' => 'contained',
        'logo_path' => null,
        'favicon_path' => null,
        'login_image_path' => null,
    ];

    protected $fillable = ['id', 'values'];

    protected $casts = ['values' => 'array'];

    public static function current(): self
    {
        return app(self::class);
    }

    /** @return array<string, mixed> */
    public function settings(): array
    {
        return array_replace(self::DEFAULTS, self::APPEARANCE_DEFAULTS, $this->values ?? []);
    }
}
