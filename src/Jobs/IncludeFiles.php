<?php

namespace Spatie\Export\Jobs;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Spatie\Export\Destination;

class IncludeFiles
{
    /** @var string[] */
    protected $includeFiles;

    /** @var string[] */
    protected $excludeFilePatterns;

    public function __construct(array $includeFiles, array $excludeFilePatterns)
    {
        $this->includeFiles = $includeFiles;
        $this->excludeFilePatterns = $excludeFilePatterns;
    }

    public function handle(Destination $destination)
    {
        foreach ($this->includeFiles as $source => $target) {
            if (is_file($source)) {
                $this->exportIncludedFile($source, $target, $destination);
            } else {
                $this->exportIncludedDirectory($source, $target, $destination);
            }
        }
    }

    protected function exportIncludedFile(string $source, string $target, Destination $destination)
    {
        if ($this->shouldExclude($source)) {
            return;
        }

        $target = '/'.ltrim($target, '/');

        $destination->write($target, file_get_contents($source));
    }

    protected function exportIncludedDirectory(string $source, string $target, Destination $destination)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            $this->exportIncludedFile(
                $item->getPathname(),
                $target.'/'.$iterator->getSubPathName(),
                $destination
            );
        }
    }

    protected function shouldExclude(string $source): bool
    {
        foreach ($this->excludeFilePatterns as $pattern) {
            if (preg_match($pattern, $source)) {
                return true;
            }
        }

        return false;
    }
}
