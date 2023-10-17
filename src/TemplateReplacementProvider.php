<?php

namespace Classid\TemplateReplacement;

use Illuminate\Support\ServiceProvider;

class TemplateReplacementProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/templatereplacement.php', 'templatereplacement');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/templatereplacement.php' => config_path('templatereplacement.php'),
        ]);
    }
}
