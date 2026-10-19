<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Otomatis gunakan HTTPS HANYA jika request datang lewat HTTPS atau reverse proxy / Cloudflare Tunnel
        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->server('HTTP_CF_RAY') || request()->isSecure()) {
            URL::forceScheme('https');
        }

        // Pastikan asset dan URL mengarah ke domain tunnel jika diakses dari luar lewat Cloudflare Tunnel
        if ($forwardedHost = request()->header('x-forwarded-host')) {
            URL::forceRootUrl('https://' . $forwardedHost);
        } elseif (request()->server('HTTP_CF_RAY') && config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }
    }
}
