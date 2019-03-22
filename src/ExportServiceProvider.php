<?php

namespace Spatie\Export;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Spatie\Export\Console\ExportCommand;

class ExportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/export.php', 'export');

        $this->app->singleton(Exporter::class, function () {
            return (new Exporter($this->getDisk()))
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

    protected function getDisk(): Filesystem
    {
        if (! config('export.disk')) {
            config([
                'filesystems.disks.laravel_export' => [
                    'driver' => 'local',
                    'root' => base_path('dist'),
                ],
            ]);

            return Storage::disk('laravel_export');
        }

        return Storage::disk(config('export.disk'));
    }
}
