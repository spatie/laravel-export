<?php

namespace Spatie\Export\Jobs;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Spatie\Export\Destination;

class IncludeFile
{
    /** @var string */
    protected $source;

    /** @var string */
    protected $target;

    /** @var string[] */
    protected $excludeFilePatterns;

    public function __construct(string $source, string $target, array $excludeFilePatterns)
    {
        $this->source = $source;
        $this->target = $target;
        $this->excludeFilePatterns = $excludeFilePatterns;
    }

    public function handle(Destination $destination)
    {
        if (is_file($this->source)) {
            $this->exportIncludedFile($this->source, $this->target, $destination);
        } elseif (is_dir($this->source)) {
            $this->exportIncludedDirectory($this->source, $this->target, $destination);
        } else {
            throw new RuntimeException("File or directory [{$this->source}] not found");
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
