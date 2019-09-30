<?php

namespace Spatie\Export\Jobs;

use Illuminate\Contracts\Routing\UrlGenerator;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Export\Crawler\Observer;
use Spatie\Export\Destination;
use Spatie\Export\Crawler\LocalClient;

class CrawlSite
{
    public function handle(UrlGenerator $urlGenerator, Destination $destination)
    {
        $entry = $urlGenerator->to('/');

        (new Crawler(new LocalClient()))
            ->setCrawlObserver(new Observer($entry, $destination))
            ->setCrawlProfile(new CrawlInternalUrls($entry))
            ->startCrawling($entry);
    }
}
