<?php

namespace Spatie\Export;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Spatie\Export\Console\ExportCommand;

class ExportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/export.php', 'export');

        $this->app->singleton(Exporter::class, function () {
            if (config('export.disk')) {
                $storage_disk = Storage::disk(config('export.disk'));
            } else {
                config([
                    'filesystems.disks.spatie_export' => [
                        'driver' => 'local',
                        'root' => base_path('dist'),
                    ],
                ]);
                $storage_disk = Storage::disk('spatie_export');
            }

            return (new Exporter($storage_disk))
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
