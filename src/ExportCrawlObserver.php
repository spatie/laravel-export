<?php

namespace Spatie\Export;

use Spatie\Crawler\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Spatie\Export\Concerns\Messenger;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Filesystem\Filesystem;

class ExportCrawlObserver extends CrawlObserver
{
    use Messenger;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $filesystem;

    /** @var string */
    protected $entry;

    /** @var string[] */
    protected $assets;

    public function __construct(Filesystem $filesystem, string $entry)
    {
        $this->filesystem = $filesystem;

        $this->entry = $entry;

        $this->assets = [];
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $isFile = ! preg_match('/^((.+\.[\d]+[^\w]*)|((?:(?!\.).)*|))$/', $url->getPath());

        $targetPath = $isFile
            ? '/'.ltrim($url->getPath(), '/')
            : '/'.ltrim($url->getPath().'/index.html', '/');

        $this->message(str_replace('http://localhost', '', $url)." => {$targetPath}");

        $contents = str_replace($this->entry.'/', '/', $response->getBody());
        $contents = str_replace($this->entry, '/', $contents);

        $this->filesystem->put($targetPath, $contents);
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        throw $requestException;
    }
}
