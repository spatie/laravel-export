<?php

namespace Spatie\Export\Crawler;

use Spatie\Export\Destination;
use Spatie\Crawler\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use Spatie\Export\Traits\NormalizesPaths;
use GuzzleHttp\Exception\RequestException;

class Observer extends CrawlObserver
{
    use NormalizesPaths;

    /** @var string */
    protected $entry;

    /** @var \Spatie\Export\Destination */
    protected $destination;

    public function __construct(string $entry, Destination $destination)
    {
        $this->entry = $entry;
        $this->destination = $destination;
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $contents = str_replace($this->entry.'/', '/', (string) $response->getBody());
        $contents = str_replace($this->entry, '/', $contents);

        $this->destination->write(
            $this->normalizePath($url->getPath()),
            $contents
        );
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        throw $requestException;
    }
}
