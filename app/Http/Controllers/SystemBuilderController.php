<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SystemBuilderController extends Controller
{
    public function edit(): View
    {
        $settings = SystemSetting::current();

        return view('settings.builder', [
            'appearance' => $settings->settings(), 'modules' => SystemSetting::MODULES,
            'moduleSettings' => $settings->values['modules'] ?? [],
            'widgets' => $settings->dashboardWidgets(), 'pages' => $settings->customPages(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => 'required|in:glass,solid',
            'font_family' => 'required|in:instrument,system,serif',
            'font_size' => 'required|in:small,standard,large',
            'spacing' => 'required|in:compact,comfortable,spacious',
            'corner_style' => 'required|in:rounded,square',
            'background_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'dashboard_title' => 'required|string|max:100',
            'modules' => ['required', 'array:'.implode(',', array_keys(SystemSetting::MODULES))],
            'modules.*' => 'required|array:admin,cashier',
            'modules.*.admin' => 'required|boolean',
            'modules.*.cashier' => 'required|boolean',
            'widgets' => 'required|array|size:7',
            'widgets.*' => 'required|array:key,title,columns,admin,cashier',
            'widgets.*.title' => 'nullable|string|max:80',
            'widgets.*.key' => ['required', 'distinct', Rule::in(array_keys(SystemSetting::DASHBOARD_WIDGETS))],
            'widgets.*.columns' => ['required', Rule::in([3, 4, 6, 8, 12])],
            'widgets.*.admin' => 'required|boolean',
            'widgets.*.cashier' => 'required|boolean',
        ]);
        foreach (SystemSetting::MODULES as $module => $definition) {
            if (! isset($validated['modules'][$module])) {
                return back()->withErrors(['modules' => 'Please include every module.'])->withInput();
            }
            $validated['modules'][$module] = [
                'admin' => (bool) $validated['modules'][$module]['admin'],
                'cashier' => $definition['cashier'] && (bool) $validated['modules'][$module]['cashier'],
            ];
        }
        foreach (['admin', 'cashier'] as $role) {
            if ($validated['modules']['pos'][$role] && ! $validated['modules']['sales'][$role]) {
                throw ValidationException::withMessages([
                    'modules.pos.'.$role => 'Enable Transactions for this role while Point of Sale is enabled, so completed sales can open their receipt.',
                ]);
            }
        }
        $values = SystemSetting::current()->values ?? [];
        foreach (array_keys(SystemSetting::APPEARANCE_DEFAULTS) as $key) {
            $values[$key] = $validated[$key];
        }
        $values['modules'] = $validated['modules'];
        $values['dashboard_widgets'] = array_values(array_map(fn (array $widget): array => [
            'key' => $widget['key'], 'title' => $widget['title'] ?? '', 'columns' => (int) $widget['columns'],
            'admin' => (bool) $widget['admin'], 'cashier' => (bool) $widget['cashier'],
        ], $validated['widgets']));
        SystemSetting::current()->fill(['values' => $values])->save();

        return redirect()->route('settings.builder.edit')->with('success', 'Appearance, dashboard, and module settings saved.');
    }

    public function destroy(): RedirectResponse
    {
        $values = SystemSetting::current()->values ?? [];
        foreach (array_merge(array_keys(SystemSetting::APPEARANCE_DEFAULTS), ['modules', 'dashboard_widgets']) as $key) {
            unset($values[$key]);
        }
        SystemSetting::current()->fill(['values' => $values])->save();

        return redirect()->route('settings.builder.edit')->with('success', 'Builder defaults restored. Branding, pages, and business data are unchanged.');
    }
}
