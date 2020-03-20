<?php

namespace Spatie\Export;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Spatie\Export\Console\ExportCommand;
use Spatie\Export\Destinations\FilesystemDestination;

class ExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/export.php', 'export');

        $this->app->singleton(Destination::class, function () {
            return new FilesystemDestination($this->getDisk());
        });

        $this->app->singleton(Exporter::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/export.php' => config_path('export.php'),
            ], 'config');

            $this->commands([
                ExportCommand::class,
            ]);
        }

        $this->app->make(Exporter::class)
            ->cleanBeforeExport(config('export.clean_before_export', false))
            ->crawl(config('export.crawl', false))
            ->paths(config('export.paths', []))
            ->includeFiles(config('export.include_files', []))
            ->excludeFilePatterns(config('export.exclude_file_patterns', []));
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
