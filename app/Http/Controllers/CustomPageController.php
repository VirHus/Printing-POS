<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomPageController extends Controller
{
    public function index(): View
    {
        return view('settings.pages', ['pages' => SystemSetting::current()->customPages()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePage($request);
        $pages = SystemSetting::current()->customPages();
        if (count($pages) >= 30) {
            throw ValidationException::withMessages(['title' => 'A maximum of 30 custom pages is supported.']);
        }
        if (collect($pages)->contains('slug', $validated['slug'])) {
            throw ValidationException::withMessages(['slug' => 'This page address is already in use.']);
        }
        $pages[] = $validated;
        $this->savePages($pages);

        return redirect()->route('settings.pages.index')->with('success', 'Custom page added. Enabled pages appear in the sidebar for the selected roles.');
    }

    public function update(Request $request, string $page): RedirectResponse
    {
        $pages = SystemSetting::current()->customPages();
        $index = collect($pages)->search(fn (array $item): bool => $item['slug'] === $page);
        abort_if($index === false, 404);
        $validated = $this->validatePage($request);
        if (collect($pages)->contains(fn (array $item): bool => $item['slug'] !== $page && $item['slug'] === $validated['slug'])) {
            throw ValidationException::withMessages(['slug' => 'This page address is already in use.']);
        }
        $pages[$index] = $validated;
        $this->savePages($pages);

        return redirect()->route('settings.pages.index')->with('success', 'Custom page updated.');
    }

    public function show(Request $request, string $page): View
    {
        $customPage = collect(SystemSetting::current()->customPages($request->user()))->firstWhere('slug', $page);
        abort_if($customPage === null, 404);

        return view('settings.page', compact('customPage'));
    }

    /** @return array<string, mixed> */
    private function validatePage(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'section' => 'required|string|max:60',
            'body' => 'required|string|max:20000',
            'enabled' => 'required|boolean', 'admin' => 'required|boolean', 'cashier' => 'required|boolean',
        ]);
        foreach (['enabled', 'admin', 'cashier'] as $key) {
            $validated[$key] = (bool) $validated[$key];
        }

        return $validated;
    }

    /** @param list<array<string, mixed>> $pages */
    private function savePages(array $pages): void
    {
        $values = SystemSetting::current()->values ?? [];
        $values['custom_pages'] = array_values($pages);
        SystemSetting::current()->fill(['values' => $values])->save();
    }
}
