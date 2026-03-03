<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Supported languages in your application.
     */
    protected array $supportedLocales = ['en', 'fr'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if lang parameter exists in request (query string, route parameter, or body)
        $locale = $request->input('lang') ?? $request->header('Accept-Language');

        // If locale is provided and supported, set it
        if ($locale && in_array($locale, $this->supportedLocales)) {
            App::setLocale($locale);
        }
        // Otherwise, use the default locale from config/app.php

        return $next($request);
    }
}
