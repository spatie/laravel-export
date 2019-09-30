<?php

namespace Spatie\Export\Jobs;

use Spatie\Export\Crawler\LocalClient;
use Spatie\Export\Destination;

class ExportPage
{
    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle(Destination $destination)
    {
        $destination->write(
            $this->path,
            (new LocalClient())->get($this->path)->getBody()
        );
    }
}
