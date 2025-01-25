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
        }
    }
}
