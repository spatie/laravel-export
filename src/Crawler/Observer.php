<?php

namespace Spatie\Export\Crawler;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;
use Spatie\Export\Destination;

class Observer extends CrawlObserver
{
    /** @var \Spatie\Export\Destination */
    protected $destination;

    /** @var string */
    protected $entry;

    public function __construct(Destination $destination, string $entry)
    {
        $this->destination = $destination;

        $this->entry = $entry;
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $contents = str_replace($this->entry.'/', '/', $response->getBody());
        $contents = str_replace($this->entry, '/', $contents);

        $this->destination->write(
            ltrim($url->getPath().'/index.html', '/'),
            $contents
        );
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        throw $requestException;
    }
}
