<?php

namespace Spatie\Export\Tests\Integration;

use Spatie\Export\Exporter;
use Illuminate\Support\Facades\Route;
use Spatie\Export\ExportServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class ExportTest extends BaseTestCase
{
    protected const HOME_CONTENT = '<a href="feed/blog.atom" title="all blogposts">Feed</a>Home <a href="about">About</a>';
    protected const ABOUT_CONTENT = 'About';
    protected const FEED_CONTENT = 'Feed';

    protected function setUp() : void
    {
        parent::setUp();

        exec('rm -r '.__DIR__.'/dist');

        Route::get('/', function () {
            return static::HOME_CONTENT;
        });

        Route::get('about', function () {
            return static::ABOUT_CONTENT;
        });

        Route::get('feed/blog.atom', function () {
            return static::FEED_CONTENT;
        });
    }

    /** @test */
    public function it_crawls_and_exports_routes()
    {
        app(Exporter::class)->export();

        $this->assertFileExists(__DIR__.'/dist/index.html');
        $this->assertEquals(
            static::HOME_CONTENT,
            file_get_contents(__DIR__.'/dist/index.html')
        );

        $this->assertFileExists(__DIR__.'/dist/about/index.html');
        $this->assertEquals(
            static::ABOUT_CONTENT,
            file_get_contents(__DIR__.'/dist/about/index.html')
        );
    }

    /** @test */
    public function it_exports_paths()
    {
        app(Exporter::class)
            ->crawl(false)
            ->paths(['/', '/about'])
            ->export();

        $this->assertFileExists(__DIR__.'/dist/index.html');
        $this->assertEquals(
            static::HOME_CONTENT,
            file_get_contents(__DIR__.'/dist/index.html')
        );

        $this->assertFileExists(__DIR__.'/dist/about/index.html');
        $this->assertEquals(
            static::ABOUT_CONTENT,
            file_get_contents(__DIR__.'/dist/about/index.html')
        );
    }

    /** @test */
    public function it_exports_included_files()
    {
        app(Exporter::class)
            ->includeFiles([__DIR__.'/public' => ''])
            ->export();

        $this->assertFileExists(__DIR__.'/dist/favicon.ico');
        $this->assertFileExists(__DIR__.'/dist/media/image.png');

        $this->assertFileNotExists(__DIR__.'/dist/index.php');
    }

    /** @test */
    public function it_exports_non_html_files()
    {
        app(Exporter::class)->export();

        $this->assertFileExists(__DIR__.'/dist/feed/blog.atom');
        $this->assertEquals(
            static::FEED_CONTENT,
            file_get_contents(__DIR__.'/dist/feed/blog.atom')
        );
    }

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
