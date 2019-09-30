<?php

namespace Spatie\Export;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Bus\Dispatcher;
use Spatie\Export\Console\ExportCommand;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\Export\Destinations\FilesystemDestination;

class ExportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/export.php', 'export');

        $this->app->singleton(Destination::class, function () {
            return new FilesystemDestination($this->getDisk());
        });

        $this->app->singleton(Exporter::class, function () {
            return (new Exporter($this->app->make(Dispatcher::class)))
                ->cleanBeforeExport(config('export.clean_before_export', false))
                ->crawl(config('export.crawl', false))
                ->paths(config('export.paths', []))
                ->includeFiles(config('export.include_files', []))
                ->excludeFilePatterns(config('export.exclude_file_patterns', []));
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
