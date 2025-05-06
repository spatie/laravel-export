<?php

namespace Spatie\Export\Jobs;

use Illuminate\Contracts\Routing\UrlGenerator;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Export\Crawler\LocalClient;
use Spatie\Export\Crawler\Observer;
use Spatie\Export\Destination;

class CrawlSite
{
    /** @var bool */
    protected $sitemap;

    public function __construct(bool $sitemap)
    {
        $this->sitemap = $sitemap;
    }

    public function handle(UrlGenerator $urlGenerator, Destination $destination): void
    {
        $entry = $urlGenerator->to('/');

        $crawler = (new Crawler(new LocalClient))
            ->setCrawlObserver(new Observer($entry, $destination, $this->sitemap))
            ->setCrawlProfile(new CrawlInternalUrls($entry))
            ->startCrawling($entry);
    }
}
