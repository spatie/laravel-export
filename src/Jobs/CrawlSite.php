<?php

namespace Spatie\Export\Jobs;

use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Routing\UrlGenerator;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Export\Crawler\LocalClient;
use Spatie\Export\Crawler\Observer;
use Spatie\Export\Destination;

class CrawlSite
{
    /** @var bool */
    protected $useStreaming;

    public function __construct(bool $useStreaming = true)
    {
        $this->useStreaming = $useStreaming;
    }

    public function handle(UrlGenerator $urlGenerator, Destination $destination): void
    {
        $entry = $urlGenerator->to('/');

        $crawler = Crawler::create($entry, [
            RequestOptions::ALLOW_REDIRECTS => false,
            'handler' => new LocalClient(),
        ]);

        if ($this->useStreaming) {
            $crawler->stream();
        }

        $crawler
            ->addObserver(new Observer($entry, $destination))
            ->crawlProfile(new CrawlInternalUrls($entry))
            ->start();
    }
}
