<?php

namespace Spatie\Export;

use Spatie\Export\Jobs\CrawlSite;
use Illuminate\Contracts\Bus\Dispatcher;
use Spatie\Export\Jobs\CleanDestination;
use Spatie\Export\Jobs\cleanBeforeExportDestination;

class Exporter
{
    /** @var \Illuminate\Contracts\Bus\Dispatcher */
    protected $dispatcher;

    /** @var boolean */
    protected $cleanBeforeExport = false;

    /** @var boolean */
    protected $crawl = false;

    /** @var string[] */
    protected $paths = [];

    /** @var string[] */
    protected $rewriteRules = [];

    /** @var string[] */
    protected $includeFiles = [];

    /** @var string[] */
    protected $excludeFilePatterns = [];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function cleanBeforeExport(bool $cleanBeforeExport): self
    {
        $this->cleanBeforeExport = $cleanBeforeExport;

        return $this;
    }

    public function crawl(bool $crawl): self
    {
        $this->crawl = $crawl;

        return $this;
    }

    public function paths(array $paths): self
    {
        $this->paths = array_merge($this->paths, $paths);

        return $this;
    }

    public function rewriteRules(array $rewriteRules): self
    {
        $this->rewriteRules = array_merge($this->rewriteRules, $rewriteRules);

        return $this;
    }

    public function includeFiles(array $includeFiles): self
    {
        $this->includeFiles = array_merge($this->includeFiles, $includeFiles);

        return $this;
    }

    public function excludeFilePatterns(array $excludeFilePatterns): self
    {
        $this->excludeFilePatterns = array_merge($this->excludeFilePatterns, $excludeFilePatterns);

        return $this;
    }

    public function export()
    {
        if ($this->cleanBeforeExport) {
            $this->dispatcher->dispatchNow(new CleanDestination());
        }

        if ($this->crawl) {
            $this->dispatcher->dispatchNow(new CrawlSite());
        }
    }
}
