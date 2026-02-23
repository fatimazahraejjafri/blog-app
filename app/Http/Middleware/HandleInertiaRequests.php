<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $language = Language::where('code', $locale)->first();

        $translations = $language
            ? Translation::where('language_id', $language->id)
                ->get()
                ->groupBy('group')
                ->map(fn($items) => $items->pluck('value', 'key'))
                ->toArray()
            : [];

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'locale' => $locale,                                                    
            'languages' => Language::where('is_active', true)->get(['name', 'code']), 
            'translations' => $translations,                                        
        ];
    }
}