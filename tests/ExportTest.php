<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Testing\Fakes\BusFake;
use Spatie\Export\Exporter;
use Spatie\Export\Jobs\CrawlSite;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFileExists;

const HOME_CONTENT = <<<'HTML'
    <a href="feed/blog.atom" title="all blogposts">Feed</a>
    Home
    <a href="about">About</a>
    <a href="redirect">Spatie</a>
HTML;

const ABOUT_CONTENT = 'About';

const FEED_CONTENT = 'Feed';

const REDIRECT_CONTENT = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='https://spatie.be'" />

        <title>Redirecting to https://spatie.be</title>
    </head>
    <body>
        Redirecting to <a href="https://spatie.be">https://spatie.be</a>.
    </body>
</html>
HTML;

function assertHomeExists(): void
{
    assertExportedFile(__DIR__.'/dist/index.html', HOME_CONTENT);
}

function assertAboutExists(): void
{
    assertExportedFile(__DIR__.'/dist/about/index.html', ABOUT_CONTENT);
}

function assertFeedBlogAtomExists(): void
{
    assertExportedFile(__DIR__.'/dist/feed/blog.atom', FEED_CONTENT);
}

function assertRedirectExists(): void
{
    assertExportedFile(__DIR__.'/dist/redirect/index.html', REDIRECT_CONTENT);
}

function assertExportedFile(string $path, string $content): void
{
    assertFileExists($path);
    // Normalize line endings for cross-platform compatibility
    $expectedContent = str_replace(["\r\n", "\r"], "\n", $content);
    $actualContent = str_replace(["\r\n", "\r"], "\n", file_get_contents($path));
    assertEquals($expectedContent, $actualContent);
}

function assertRequestsHasHeader(): void
{
    expect(Route::getCurrentRequest()->header('X-Laravel-Export'))->toEqual('true');
}

beforeEach(function () {
    $this->distDirectory = __DIR__.DIRECTORY_SEPARATOR.'dist';

    if (file_exists($this->distDirectory)) {
        exec(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'rmdir "'.$this->distDirectory.'" /s /q'
            : 'rm -r "'.$this->distDirectory.'"');
    }

    Route::get('/', function () {
        return HOME_CONTENT;
    });

    Route::get('about', function () {
        return ABOUT_CONTENT;
    });

    Route::get('feed/blog.atom', function () {
        return FEED_CONTENT;
    });

    Route::redirect('redirect', 'https://spatie.be');
});

afterEach(function () {
    if (Bus::getFacadeRoot() instanceof BusFake) {
        // Disable file-based assertions when bus is faked
        return;
    }

    assertHomeExists();
    assertAboutExists();
    assertFeedBlogAtomExists();
    assertRedirectExists();
    assertRequestsHasHeader();
});

it('crawls and exports routes', function () {
    app(Exporter::class)
        ->crawl(true)
        ->export();
});

it('crawls and exports routes while using streaming', function () {
   app(Exporter::class)
        ->crawl(true)
        ->useStreaming(true)
        ->export();
});

it('can enable streaming', function () {
    $this->app->forgetInstance(Exporter::class);
    Bus::fake();

    app(Exporter::class)
        ->crawl(true)
        ->useStreaming(true)
        ->export();

    Bus::assertDispatched(CrawlSite::class, function (CrawlSite $job) {
        return $job->useStreaming() === true;
    });
});

it('exports paths', function () {
    app(Exporter::class)
        ->crawl(false)
        ->paths(['/', '/about', '/feed/blog.atom', '/redirect'])
        ->export();
});

it('exports urls', function () {
    app(Exporter::class)
        ->crawl(false)
        ->urls([url('/'), url('/about'), url('/feed/blog.atom'), url('/redirect')])
        ->export();
});

it('exports mixed', function () {
    app(Exporter::class)
        ->crawl(false)
        ->paths('/')
        ->urls(url('/about'), url('/feed/blog.atom'), url('/redirect'))
        ->export();
});

it('exports included files', function () {
    app(Exporter::class)
        ->includeFiles([__DIR__.'/stubs/public' => ''])
        ->export();

    assertFileExists(__DIR__.'/dist/favicon.ico');
    assertFileExists(__DIR__.'/dist/media/image.png');

    expect(file_exists(__DIR__.'/dist/index.php'))->toBeFalse();
});

it('exports paths with query parameters', function () {
    // Set up a simple route with query parameters
    Route::get('test-categories', function () {
        $page = request('page', 1);

        return "Test Categories page {$page}";
    });

    // Also set up the default routes that afterEach expects
    Route::get('/', function () {
        return HOME_CONTENT;
    });
    Route::get('about', function () {
        return ABOUT_CONTENT;
    });
    Route::get('feed/blog.atom', function () {
        return FEED_CONTENT;
    });
    Route::redirect('redirect', 'https://spatie.be');

    $paths = [
        '/',                      // Required by afterEach
        '/about',                 // Required by afterEach
        '/feed/blog.atom',        // Required by afterEach
        '/redirect',              // Required by afterEach
    ];

    // Add test-categories with page query from 1 to 7
    $maxPage = 7;
    foreach (range(1, $maxPage) as $page) {
        $paths[] = "/test-categories?page={$page}";
    }

    app(Exporter::class)
        ->crawl(false)
        ->paths($paths)
        ->export();

    // Check if files are created and content is correct for each page
    foreach (range(1, $maxPage) as $page) {
        $expectedPath = __DIR__."/dist/test-categories/page={$page}/index.html";
        expect(file_exists($expectedPath))->toBeTrue("Expected file not found: {$expectedPath}");
        expect(file_get_contents($expectedPath))->toBe("Test Categories page {$page}");
    }
});
