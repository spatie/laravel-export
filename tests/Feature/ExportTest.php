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

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('del '.__DIR__.'\dist /q');
        } else {
            exec('rm -r '.__DIR__.'/dist');        
        }

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
    public function it_crawls_and_exports_routes(): void
    {
        app(Exporter::class)->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_paths(): void
    {
        app(Exporter::class)
            ->crawl(false)
            ->paths(['/', '/about', '/feed/blog.atom'])
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_urls(): void
    {
        app(Exporter::class)
            ->crawl(false)
            ->urls([url('/'), url('/about'), url('/feed/blog.atom')])
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_mixed(): void
    {
        app(Exporter::class)
            ->crawl(false)
            ->paths('/')
            ->urls(url('/about'), url('/feed/blog.atom'))
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_included_files()
    {
        app(Exporter::class)
            ->includeFiles([__DIR__.'/public' => ''])
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();

        $this->assertFileExists(__DIR__.'/dist/favicon.ico');
        $this->assertFileExists(__DIR__.'/dist/media/image.png');

        $this->assertFileNotExists(__DIR__.'/dist/index.php');
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

    protected static function assertHomeExists(): void
    {
        static::assertExportedFile(__DIR__.'/dist/index.html', static::HOME_CONTENT);
    }

    protected static function assertAboutExists(): void
    {
        static::assertExportedFile(__DIR__.'/dist/about/index.html', static::ABOUT_CONTENT);
    }

    protected static function assertFeedBlogAtomExists(): void
    {
        static::assertExportedFile(__DIR__.'/dist/feed/blog.atom', static::FEED_CONTENT);
    }

    protected static function assertExportedFile(string $path, string $content): void
    {
        static::assertFileExists($path);
        static::assertEquals($content, file_get_contents($path));
    }
}
