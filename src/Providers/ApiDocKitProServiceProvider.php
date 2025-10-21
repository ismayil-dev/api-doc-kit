<?php

namespace IsmayilDev\ApiDocKit\Providers;

use Illuminate\Support\ServiceProvider;
use IsmayilDev\ApiDocKit\Console\Commands\GenerateDocCommand;
use IsmayilDev\ApiDocKit\Console\Commands\ScanModelsCommand;

class ApiDocKitProServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDocCommand::class,
                ScanModelsCommand::class,
            ]);

            // Publish config file
            $this->publishes([
                __DIR__.'/../../config/api-doc-kit.php' => config_path('api-doc-kit.php'),
            ], 'api-doc-kit-config');
        }
    }

    public function register()
    {
        // Merge package config with published config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/api-doc-kit.php',
            'api-doc-kit'
        );
    }
}
