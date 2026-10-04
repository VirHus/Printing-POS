<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SidebarSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.sidebar', ['items' => SystemSetting::current()->sidebarItems(), 'destinations' => SystemSetting::SIDEBAR_DESTINATIONS, 'icons' => SystemSetting::SIDEBAR_ICONS]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1|max:30',
            'items.*' => 'required|array:label,section,destination,url,icon,admin,cashier',
            'items.*.label' => 'required|string|max:60',
            'items.*.section' => 'nullable|string|max:60',
            'items.*.destination' => ['required', Rule::in(array_keys(SystemSetting::SIDEBAR_DESTINATIONS))],
            'items.*.url' => 'nullable|required_if:items.*.destination,external|url:http,https|max:2048',
            'items.*.icon' => ['required', Rule::in(array_keys(SystemSetting::SIDEBAR_ICONS))],
            'items.*.admin' => 'required|boolean',
            'items.*.cashier' => 'required|boolean',
        ]);
        $items = array_map(fn (array $item): array => [
            'label' => $item['label'], 'section' => $item['section'] ?? '', 'destination' => $item['destination'],
            'url' => $item['destination'] === 'external' ? $item['url'] : '', 'icon' => $item['icon'],
            'admin' => (bool) $item['admin'], 'cashier' => (bool) $item['cashier'],
        ], array_values($validated['items']));
        $settings = SystemSetting::current();
        $values = $settings->values ?? [];
        $values['sidebar_items'] = $items;
        $settings->fill(['values' => $values])->save();

        return redirect()->route('settings.sidebar.edit')->with('success', 'Sidebar saved. Changes are now visible to administrators and cashiers.');
    }

    public function destroy(): RedirectResponse
    {
        $settings = SystemSetting::current();
        $values = $settings->values ?? [];
        unset($values['sidebar_items']);
        $settings->fill(['values' => $values])->save();

        return redirect()->route('settings.sidebar.edit')->with('success', 'Default sidebar restored.');
    }
}
