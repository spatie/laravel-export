<?php

namespace Spatie\Export\Jobs;

use Illuminate\Contracts\Routing\UrlGenerator;
use Spatie\Crawler\Crawler;
use Spatie\Export\Crawler\Observer;
use Spatie\Export\Destination;
use Spatie\Export\Crawler\LocalClient;
use Spatie\Crawler\CrawlInternalUrls;

class CrawlSite
{
    public function handle(UrlGenerator $urlGenerator, Destination $destination)
    {
        $entry = $urlGenerator->to('/');

        (new Crawler(new LocalClient()))
            ->setCrawlObserver(new Observer($destination, $entry))
            ->setCrawlProfile(new CrawlInternalUrls($entry))
            ->startCrawling($entry);
    }
}
