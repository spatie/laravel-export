<?php

namespace Spatie\Export;

use Spatie\Export\Console\ExportCommand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class ExportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/export.php', 'export');

        $this->app->singleton(Exporter::class, function () {
            return (new Exporter(Storage::disk('export')))
                ->entries(config('export.entries', []))
                ->include(config('export.include', []))
                ->exclude(config('export.exclude', []));
        });

        $this->commands([
            ExportCommand::class,
        ]);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/export.php' => config_path('export.php'),
            ], 'config');
        }
    }
}
