<?php

namespace Spatie\Export\Console;

use Spatie\Export\Exporter;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ExportCommand extends Command
{
    protected $signature = 'export {--skip-before} {--skip-after}';

    protected $description = 'Export the entire app to a static site';

    public function handle(Exporter $exporter)
    {
        $exporter->onMessage(function (string $message) {
            $this->comment($message, 'v');
        });

        if (! $this->input->getOption('skip-before')) {
            $this->runBeforeHooks();
        }

        $this->info('Starting export...');

        $exporter->export();

        if (config('export.disk')) {
            $this->info('Files were saved to disk `'.config('export.disk').'`');
        } else {
            $this->info('Files were saved to `dist`');
        }

        if (! $this->input->getOption('skip-after')) {
            $this->runAfterHooks();
        }
    }

    protected function runBeforeHooks()
    {
        $beforeHooks = config('export.before');

        if (! count($beforeHooks)) {
            return;
        }

        $this->info('Running before hooks...');

        $this->runHooks($beforeHooks);
    }

    protected function runAfterHooks()
    {
        $afterHooks = config('export.after');

        if (! count($afterHooks)) {
            return;
        }

        $this->info('Running after hooks...');

        $this->runHooks($afterHooks);
    }

    protected function runHooks(array $hooks)
    {
        foreach ($hooks as $command) {
            $this->comment("[{$command}]", 'v');

            $process = new Process($command);

            $process->mustRun();

            foreach ($process as $data) {
                $this->output->write($data);
            }
        }
    }
}
