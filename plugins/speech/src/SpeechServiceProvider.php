<?php

namespace Nativephp\Speech;

use Illuminate\Support\ServiceProvider;
use Nativephp\Speech\Commands\CopyAssetsCommand;

class SpeechServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Speech::class, function () {
            return new Speech();
        });
    }

    public function boot(): void
    {
        // Register plugin hook commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CopyAssetsCommand::class,
            ]);
        }
    }
}