<?php

namespace Spatie\Export\Jobs;

use Illuminate\Http\Request;
use Spatie\Export\Destination;
use Illuminate\Contracts\Http\Kernel;
use RuntimeException;
use Spatie\Export\Crawler\LocalClient;

class ExportPath
{
    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle(Kernel $kernel, Destination $destination)
    {
        $response = $kernel->handle(
            Request::create($this->path)
        );

        if ($response->status() !== 200) {
            throw new RuntimeException("Path [{$this->path}] returned status code [{$response->status()}]");
        }

        $destination->write($this->path . '/index.html', $response->content());
    }
}
