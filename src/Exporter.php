<?php

namespace Spatie\Export;

use Spatie\Export\Concerns\Messenger;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Contracts\Filesystem\Filesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlInternalUrls;

class Exporter
{
    use Messenger;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $filesystem;

    /** @var \GuzzleHttp\Psr7\Uri */
    protected $baseUrl;

    /** @var \Spatie\Export\ExportCrawlObserver */
    protected $crawlObserver;

    public function __construct(Filesystem $filesystem, string $baseUrl)
    {
        $this->filesystem = $filesystem;

        $this->baseUrl = new Uri($baseUrl);

        $this->crawlObserver = new ExportCrawlObserver($filesystem, $this->baseUrl);
    }

    public function export(): void
    {
        Crawler::create()
            ->setCrawlProfile(new CrawlInternalUrls($this->baseUrl))
            ->setCrawlObserver($this->crawlObserver)
            ->startCrawling($this->baseUrl);

        $this->exportAssets();
    }

    public function onMessage(callable $onMessage): void
    {
        $this->onMessage = $onMessage;

        $this->crawlObserver->onMessage($onMessage);
    }

    protected function exportAssets()
    {
        $this->message("Exporting public directory");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(public_path(), RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if (!$item->isDir() && $item->getExtension() !== "php") {
                $this->filesystem->put(
                    $iterator->getSubPathName(),
                    file_get_contents($item->getPathname())
                );
             }
        }
    }
}
