<?php

namespace Spatie\Export;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use App\Http\Kernel;
use Illuminate\Http\Request;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\Export\Concerns\Messenger;

class Exporter
{
    use Messenger;

    /** @var \App\Http\Kernel */
    protected $kernel;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $filesystem;

    /** @var string[] */
    protected $paths = [];

    /** @var string[] */
    protected $include = [];

    /** @var string[] */
    protected $exclude = [];

    /** @var \Illuminate\Console\Command */
    protected $cli;

    public function __construct(Kernel $kernel, Filesystem $filesystem)
    {
        $this->kernel = $kernel;
        $this->filesystem = $filesystem;
    }

    public function paths(array $paths): Exporter
    {
        $this->paths = array_merge($this->paths, $paths);

        return $this;
    }

    public function include(array $include): Exporter
    {
        $this->include = array_merge($this->include, $include);

        return $this;
    }

    public function exclude(array $exclude): Exporter
    {
        $this->exclude = array_merge($this->exclude, $exclude);

        return $this;
    }

    public function export(): void
    {
        $this->exportPaths();

        $this->exportIncludedFiles();
    }

    protected function exportPaths(): void
    {
        $this->cli->comment("Exporting paths...");

        $progressBar = $this->cli->getOutput()->createProgressBar(count($this->paths));

        $progressBar->start();

        foreach ($this->paths as $path) {
            $response = $this->kernel->handle(
                Request::create($path, 'GET')
            );

            $targetPath = '/'.ltrim($path . '/index.html', '/');

            $progressBar->advance();

            $contents = str_replace('http://localhost/', '/', $response->content());
            $contents = str_replace('http://localhost', '/', $contents);

            $this->filesystem->put($targetPath, $contents);
        }

        $progressBar->finish();
    }

    protected function exportIncludedFiles(): void
    {
        $this->cli->comment("\nExporting files...");

        foreach ($this->include as ['source' => $source, 'target' => $target]) {
            if (is_file($source)) {
                $this->exportIncludedFile($source, $target);
            } else {
                $this->exportIncludedDirectory($source, $target);
            }
        }
    }

    protected function exportIncludedFile(string $source, string $target): void
    {
        if ($this->excludes($source)) {
            return;
        }

        $target = '/'.ltrim($target, '/');

        $this->filesystem->put($target, file_get_contents($source));
    }

    protected function exportIncludedDirectory(string $source, string $target): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            $this->exportIncludedFile($item->getPathname(), $target.'/'.$iterator->getSubPathName());
        }
    }

    protected function excludes(string $source): bool
    {
        foreach ($this->exclude as $pattern) {
            if (preg_match($pattern, $source)) {
                return true;
            }
        }

        return false;
    }

    public function setCli(Command $cli)
    {
        $this->cli = $cli;
    }
}
