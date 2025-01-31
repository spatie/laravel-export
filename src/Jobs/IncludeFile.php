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
            // No checking for isLink() would result in the contents of symlink'd directories
            // to not be exported on UNIX machines, and the symlink itself to be excluded.
            if ($item->isDir() && !$item->isLink()) {
                continue;
            }

            // Checking isDir() and isLink() on $item could lead to false negatives due to the way
            // symlinks are handled on Windows, especially if created under Git-For-Windows.
            // When debugging it could lead to isDir(), isFile() and isLink() to all 3 be false.
            // Despite that, getRealPath() does return the resolved symlink path.
            // Hence why we check on $realItem instead.
            // Copying Windows "symlinks" as files would also be "wrong" and not portable across file systems.
            $realItem = new \SplFileInfo($item->getRealPath());
            if ($realItem->isDir()) {
                // Since it was a symlink, the subfiles weren't crawled recursively
                // so we need to "recursively" export that directory in our export
                // destination to maintain the expected file structure, and to copy
                // absolutely all the files we needed to copy.
                $this->exportIncludedDirectory(
                    $realItem->getPathname(),
                    $target.'/'.$iterator->getSubPathName(),
                    $destination
                );
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
