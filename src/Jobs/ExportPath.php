<?php

namespace Spatie\Export\Jobs;

use Illuminate\Http\Request;
use Spatie\Export\Destination;
use Illuminate\Contracts\Http\Kernel;
use RuntimeException;
use Spatie\Export\Crawler\LocalClient;
use Spatie\Export\Traits\NormalizesPath;

class ExportPath
{
    use NormalizesPath;

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

        $destination->write($this->normalizePath($this->path), $response->content());
    }
}
