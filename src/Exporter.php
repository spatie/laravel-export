<?php

namespace Spatie\Export;

use Spatie\Crawler\Crawler;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Export\Concerns\Messenger;
use Illuminate\Contracts\Filesystem\Filesystem;

class Exporter
{
    use Messenger;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $filesystem;

    /** @var string[] */
    protected $entries;

    /** @var string[] */
    protected $include;

    /** @var string[] */
    protected $exclude;

    /** @var \Spatie\Crawler\Crawler */
    protected $crawler;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->crawler = (new Crawler(new InternalClient()));
    }

    public function entries(array $entries): Exporter
    {
        $this->entries = array_map(function (string $entry) {
            return url($entry);
        }, $entries);

        return $this;
    }

    public function include(array $include): Exporter
    {
        $this->include = array_map(function ($include) {
            return is_array($include)
                ? $include
                : ['source' => $include, 'target' => $include];
        }, $include);

        return $this;
    }

    public function exclude(array $exclude): Exporter
    {
        $this->exclude = $exclude;

        return $this;
    }

    public function export(): void
    {
        $this->exportEntries();

        $this->exportIncludedFiles();
    }

    protected function exportEntries(): void
    {
        foreach ($this->entries as $entry) {
            $this->message("[{$entry}]");

            $crawlObserver = new ExportCrawlObserver($this->filesystem, $entry);
            $crawlObserver->onMessage($this->onMessage);

            $this->crawler
                ->setCrawlObserver($crawlObserver)
                ->setCrawlProfile(new CrawlInternalUrls($entry))
                ->startCrawling($entry);
        }
    }

    protected function exportIncludedFiles(): void
    {
        foreach ($this->include as ['source' => $source, 'target' => $target]) {
            $this->message("[{$source}]");

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

        $this->message($target);

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
}
