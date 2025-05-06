<?php

namespace Spatie\Export;
use Spatie\Export\Destination;
use Illuminate\Contracts\Routing\UrlGenerator;

class SitemapGenerator
{

    /** @var string[] */
    protected $urls;

    public function __construct($urls)
    {
        $this->urls = $urls;
    }

    public function handle(UrlGenerator $urlGenerator, Destination $destination): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($this->urls as $url) {
            $fullUrl = $urlGenerator->to($url);
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($fullUrl) . '</loc>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }
        $xml .= '</urlset>' . PHP_EOL;
        $destination->write('/sitemap.xml', $xml);
    }
}
