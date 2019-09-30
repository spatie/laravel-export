<?php

namespace Spatie\Export\Console;

use Spatie\Export\Exporter;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;

class ExportCommand extends Command
{
    protected $name = 'export';

    protected $description = 'Export the entire app to a static site';

    public function __construct()
    {
        parent::__construct();

        collect()
            ->merge(config('export.before', []))
            ->merge(config('export.after', []))
            ->keys()
            ->unique()
            ->sort()
            ->each(function (string $name) {
                $this->addOption(
                    "skip-{$name}",
                    null,
                    InputOption::VALUE_NONE,
                    "Skip the {$name} hook"
                );
            });
    }

    public function handle(Exporter $exporter)
    {
        $this->runBeforeHooks();

        $this->info('Exporting site...');

        $exporter->export();

        if (config('export.disk')) {
            $this->info('Files were saved to disk `'.config('export.disk').'`');
        } else {
            $this->info('Files were saved to `dist`');
        }

        $this->runAfterHooks();
    }

    protected function runBeforeHooks()
    {
        $beforeHooks = collect(config('export.before', []))
            ->reject(function (string $hook, string $name) {
                return $this->input->getOption("skip-{$name}");
            });

        if (! count($beforeHooks)) {
            return;
        }

        $this->info('Running before hooks...');

        $this->runHooks($beforeHooks);
    }

    protected function runAfterHooks()
    {
        $afterHooks = collect(config('export.after', []))
            ->reject(function (string $hook, string $name) {
                return $this->input->getOption("skip-{$name}");
            });

        if (! count($afterHooks)) {
            return;
        }

        $this->info('Running after hooks...');

        $this->runHooks($afterHooks);
    }

    protected function runHooks(Collection $hooks)
    {
        foreach ($hooks as $name => $command) {
            $this->comment("[{$name}]", 'v');

            $process = new Process($command);

            $process->mustRun();

            foreach ($process as $data) {
                $this->output->write($data);
            }
        }
    }
}
