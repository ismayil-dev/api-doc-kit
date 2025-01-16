<?php

namespace IsmayilDev\LaravelDocKit\Providers;

use Illuminate\Support\ServiceProvider;
use IsmayilDev\LaravelDocKit\Console\Commands\GenerateDocCommand;

class LaraDocKitProServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDocCommand::class,
            ]);
        }
    }
}