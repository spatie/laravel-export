<?php

use Illuminate\Support\Facades\Route;
use Spatie\Export\Exporter;

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
    assertExportedFile(__DIR__ . '/dist/index.html', HOME_CONTENT);
}

function assertAboutExists(): void
{
    assertExportedFile(__DIR__ . '/dist/about/index.html', ABOUT_CONTENT);
}

function assertFeedBlogAtomExists(): void
{
    assertExportedFile(__DIR__ . '/dist/feed/blog.atom', FEED_CONTENT);
}

function assertRedirectExists(): void
{
    assertExportedFile(__DIR__ . '/dist/redirect/index.html', REDIRECT_CONTENT);
}

function assertExportedFile(string $path, string $content): void
{
    assertFileExists($path);
    assertEquals($content, file_get_contents($path));
}

function assertRequestsHasHeader(): void
{
    expect(Route::getCurrentRequest()->header('X-Laravel-Export'))->toEqual('true');
}

beforeEach(function () {
    $this->distDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'dist';

    if (file_exists($this->distDirectory)) {
        exec(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'del ' . $this->distDirectory . ' /q'
            : 'rm -r ' . $this->distDirectory);
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
    assertHomeExists();
    assertAboutExists();
    assertFeedBlogAtomExists();
    assertRedirectExists();
    assertRequestsHasHeader();
});

it('crawls and exports routes', function () {
    app(Exporter::class)->export();
    assertFileExists(__DIR__ . '/dist/sitemap.xml');
});

it('exports paths', function () {
    app(Exporter::class)
        ->crawl(false)
        ->paths(['/', '/about', '/feed/blog.atom', '/redirect'])
        ->export();
    assertFileExists(__DIR__ . '/dist/sitemap.xml');
});

it('exports urls', function () {
    app(Exporter::class)
        ->crawl(false)
        ->urls([url('/'), url('/about'), url('/feed/blog.atom'), url('/redirect')])
        ->export();
    assertFileExists(__DIR__ . '/dist/sitemap.xml');
});

it('exports mixed', function () {
    app(Exporter::class)
        ->crawl(false)
        ->paths('/')
        ->urls(url('/about'), url('/feed/blog.atom'), url('/redirect'))
        ->export();

    assertFileExists(__DIR__ . '/dist/sitemap.xml');
});

it('exports included files', function () {
    app(Exporter::class)
        ->includeFiles([__DIR__ . '/stubs/public' => ''])
        ->export();

    assertFileExists(__DIR__ . '/dist/favicon.ico');
    assertFileExists(__DIR__ . '/dist/media/image.png');
    assertFileExists(__DIR__ . '/dist/sitemap.xml');

    expect(file_exists(__DIR__ . '/dist/index.php'))->toBeFalse();
});

it('exports incliuding sitemap option', function () {
    app(Exporter::class)
        ->includeFiles([__DIR__ . '/stubs/public' => ''])
        ->export(true);

    assertFileExists(__DIR__ . '/dist/favicon.ico');
    assertFileExists(__DIR__ . '/dist/media/image.png');
    assertFileExists(__DIR__ . '/dist/sitemap.xml');

    expect(file_exists(__DIR__ . '/dist/index.php'))->toBeFalse();
});
