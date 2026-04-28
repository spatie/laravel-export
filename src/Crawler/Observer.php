<?php

namespace Spatie\Export\Crawler;

use GuzzleHttp\Exception\RequestException;
use Spatie\Crawler\CrawlProgress;
use Spatie\Crawler\CrawlResponse;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
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

    public function crawled(string $url, CrawlResponse $response, CrawlProgress $progress): void
    {
        if (! $response->isSuccessful() && ! $response->isRedirect()) {
            $foundOnUrl = $response->foundOnUrl();

            if (! empty($foundOnUrl)) {
                throw new \RuntimeException("URL [{$url}] found on [{$foundOnUrl}] returned status code [{$response->status()}]");
            }

            throw new \RuntimeException("URL [{$url}] returned status code [{$response->status()}]");
        }

        $this->destination->write(
            $this->normalizePath(parse_url($url, PHP_URL_PATH) ?? '/'),
            $response->body()
        );
    }

    public function crawlFailed(
        string $url,
        RequestException $requestException,
        CrawlProgress $progress,
        ?string $foundOnUrl = null,
        ?string $linkText = null,
        ?\Spatie\Crawler\Enums\ResourceType $resourceType = null,
        ?\Spatie\Crawler\TransferStatistics $transferStats = null,
    ): void {
        throw $requestException;
    }

}
