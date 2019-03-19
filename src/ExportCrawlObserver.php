<?php

namespace Spatie\Export;

use Spatie\Export\Concerns\Messenger;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;

class ExportCrawlObserver extends CrawlObserver
{
    use Messenger;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $filesystem;

    /** @var \Psr\Http\Message\UriInterface */
    protected $baseUrl;

    /** @var string[] */
    protected $assets;

    public function __construct(Filesystem $filesystem, UriInterface $baseUrl)
    {
        $this->filesystem = $filesystem;

        $this->baseUrl = $baseUrl;

        $this->assets = [];
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null) {
        $targetPath = '/' . ltrim($url->getPath() . '/index.html', '/');

        $this->message($targetPath);

        $contents = str_replace($this->baseUrl . '/', '/', $response->getBody());
        $contents = str_replace($this->baseUrl, '/', $contents);

        $this->filesystem->put($targetPath, $contents);
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null) {
        throw $requestException;
    }
}
