<?php

namespace App\Http\Controllers;

use App\Models\BacSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BacSettingController extends Controller
{
    /**
     * @return array<string, array{value: string, description: string}>
     */
    protected function defaults(): array
    {
        return [
            'clo_attainment_threshold' => [
                'value' => '60',
                'description' => 'Default CLO attainment threshold percentage for BAC compliance.',
            ],
            'direct_assessment_weight' => [
                'value' => '80',
                'description' => 'Default direct assessment weight percentage for BAC reporting.',
            ],
            'indirect_assessment_weight' => [
                'value' => '20',
                'description' => 'Default indirect assessment weight percentage for BAC reporting.',
            ],
        ];
    }

    public function index(): View
    {
        $existing = BacSetting::query()
            ->whereIn('key', array_keys($this->defaults()))
            ->get()
            ->keyBy('key');

        $settings = collect($this->defaults())->mapWithKeys(function (array $default, string $key) use ($existing) {
            return [$key => (object) [
                'key' => $key,
                'value' => $existing->get($key)?->value ?? $default['value'],
                'description' => $existing->get($key)?->description ?? $default['description'],
            ]];
        });

        return view('content.bac.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = array_keys($this->defaults());

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.clo_attainment_threshold' => ['required', 'numeric', 'min:0', 'max:100'],
            'settings.direct_assessment_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'settings.indirect_assessment_weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($keys as $key) {
            BacSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $validated['settings'][$key],
                    'description' => $this->defaults()[$key]['description'],
                ]
            );
        }

        return redirect()->route('bac-settings.index')->with('success', __('BAC settings updated successfully.'));
    }

}
