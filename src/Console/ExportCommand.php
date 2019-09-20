<?php

namespace Spatie\Export\Console;

use Illuminate\Support\Facades\Storage;
use Spatie\Crawler\Crawler;
use Spatie\Export\Exporter;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Export\InternalClient;
use Spatie\Sitemap\SitemapGenerator;
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
        $exporter->onMessage(function (string $message) {
            $this->comment($message, 'v');
        });

        $this->runBeforeHooks();

        $this->info('Starting export...');

        $exporter->export();

        if (config('export.sitemap.enabled')) {
            SitemapGenerator::create('https://memories2digital.com.au')
                ->configureCrawler(function (Crawler $crawler) {
                    // TODO pr to the crawler repo to let us customise the client :P
                    $reflection = new \ReflectionClass($crawler);
                    $property = $reflection->getProperty('client');
                    $property->setAccessible(true);
                    $property->setValue($crawler, new InternalClient());

                    return $crawler;

                })
                ->writeToFile(Storage::disk('local')->path('export_temp/' . config('export.sitemap.filename')));

            // Copy all of the sitemaps to the correct disk
            foreach (Storage::disk('local')->files('export_temp') as $filename) {
                $exporter->getFilesystem()->put(basename($filename), Storage::disk('local')->get($filename));
            }

            Storage::disk('local')->delete('export_temp');
        }

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
