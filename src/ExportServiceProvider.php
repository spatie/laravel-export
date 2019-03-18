<?php

namespace Spatie\Export;

use Spatie\Export\Console\ExportCommand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class ExportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Exporter::class, function () {
            return new Exporter(Storage::disk('export'), url('/'));
        });

        $this->commands([
            ExportCommand::class,
        ]);
    }
}
