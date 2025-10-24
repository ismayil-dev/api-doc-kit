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

        // Example: Customize OpenAPI servers
        // Developers can override this in their AppServiceProvider
        //
        // $this->app->bind('api-doc-kit.servers', function () {
        //     return [
        //         new \OpenApi\Attributes\Server(
        //             url: config('app.url'),
        //             description: 'Production API'
        //         ),
        //         new \OpenApi\Attributes\Server(
        //             url: 'https://staging.example.com',
        //             description: 'Staging',
        //             variables: [
        //                 new \OpenApi\Attributes\ServerVariable(
        //                     serverVariable: 'version',
        //                     default: 'v1',
        //                     enum: ['v1', 'v2'],
        //                     description: 'API Version'
        //                 ),
        //             ]
        //         ),
        //     ];
        // });
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
