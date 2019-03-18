<?php

namespace Spatie\Export\Console;

use Spatie\Export\Exporter;
use Illuminate\Console\Command;

class ExportCommand extends Command
{
    protected $signature = 'export';

    protected $description = 'Export the entire app to a static site';

    public function handle(Exporter $exporter)
    {
        $exporter->onMessage(function (string $message) {
            $this->comment($message);
        });

        $this->info("Starting export...");

        $exporter->export();

        $this->info("Done! Files were saved in the `out` directory.");
    }
}
