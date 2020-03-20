<?php

namespace Spatie\Export\Jobs;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use RuntimeException;
use Spatie\Export\Destination;
use Spatie\Export\Traits\NormalizedPath;

class ExportPath
{
    use NormalizedPath;

    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle(Kernel $kernel, Destination $destination, UrlGenerator $urlGenerator)
    {
        $response = $kernel->handle(
            Request::create($urlGenerator->to($this->path))
        );

        if ($response->status() !== 200) {
            throw new RuntimeException("Path [{$this->path}] returned status code [{$response->status()}]");
        }

        $destination->write($this->normalizePath($this->path), $response->content());
    }
}
