<?php

namespace Spatie\Export\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Export\ExportServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [ExportServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.export', [
            'driver' => 'local',
            'root' => __DIR__.'/dist',
        ]);

        $app['config']->set('export.disk', 'export');
        $app['config']->set('export.include_files', []);
    }
}
