<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Configurar encoding UTF-8 para multibyte strings
        mb_internal_encoding('UTF-8');

        // Configurar locale español para Carbon (fechas)
        \Carbon\Carbon::setLocale('es');
    }
}
