<?php

namespace Spatie\Export\Crawler;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Spatie\Crawler\CrawlObserver;
use Spatie\Export\Destination;
use Spatie\Export\Traits\NormalizedPath;

class Observer extends CrawlObserver
{
    use NormalizedPath;

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
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("URL [{$url}] returned status code [{$response->getStatusCode()}]");
        }

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
