<?php

namespace Spatie\Export\Crawler;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Export\Destination;
use Illuminate\Contracts\Routing\UrlGenerator;
use Spatie\Export\Traits\NormalizedPath;
use Spatie\Export\SitemapGenerator;

class Observer extends CrawlObserver
{
    use NormalizedPath;

    /** @var string */
    protected $entry;

    /** @var \Spatie\Export\Destination */
    protected $destination;

    /** @var bool */
    protected $sitemap;

    /** @var array */
    protected $crawledUrls = [];

    public function __construct(string $entry, Destination $destination, bool $sitemap)
    {
        $this->entry = $entry;
        $this->destination = $destination;
        $this->sitemap = $sitemap;
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        $this->crawledUrls[] = (string) $url->getPath();

        if (!$this->isSuccesfullOrRedirect($response->getStatusCode())) {
            if (!empty($foundOnUrl)) {
                throw new RuntimeException("URL [{$url}] found on [{$foundOnUrl}] returned status code [{$response->getStatusCode()}]");
            }

            throw new RuntimeException("URL [{$url}] returned status code [{$response->getStatusCode()}]");
        }

        $this->destination->write(
            $this->normalizePath($url->getPath()),
            (string) $response->getBody()
        );
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        throw $requestException;
    }

    protected function isSuccesfullOrRedirect(int $statusCode): bool
    {
        return in_array($statusCode, [200, 301, 302]);
    }

    public function finishedCrawling(): void
    {
        if ($this->sitemap) {
            $siteGen = new SitemapGenerator($this->crawledUrls);
            $siteGen->handle(app(UrlGenerator::class), app(Destination::class));
        }
    }
}
