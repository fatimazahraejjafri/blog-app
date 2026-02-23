<?php
namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale');

        if (!$locale) {
            $default = Language::where('is_default', true)->first();
            $locale = $default?->code ?? config('app.locale');
        }

        app()->setLocale($locale);
        return $next($request);
    }
}