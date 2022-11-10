<?php

use Illuminate\Support\Facades\Route;

use Spatie\Export\Exporter;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFileExists;

const HOME_CONTENT = '<a href="feed/blog.atom" title="all blogposts">Feed</a>Home <a href="about">About</a>';
const ABOUT_CONTENT = 'About';
const FEED_CONTENT = 'Feed';

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

function assertExportedFile(string $path, string $content): void
{
    assertFileExists($path);
    assertEquals($content, file_get_contents($path));
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
});

afterEach(function () {
    assertHomeExists();
    assertAboutExists();
    assertFeedBlogAtomExists();
});

it('crawls and exports routes', function () {
    app(Exporter::class)->export();
});

it('exports paths', function () {
    app(Exporter::class)
        ->crawl(false)
        ->paths(['/', '/about', '/feed/blog.atom'])
        ->export();
});

it('exports urls', function () {
    app(Exporter::class)
        ->crawl(false)
        ->urls([url('/'), url('/about'), url('/feed/blog.atom')])
        ->export();
});

it('exports mixed', function () {
    app(Exporter::class)
        ->crawl(false)
        ->paths('/')
        ->urls(url('/about'), url('/feed/blog.atom'))
        ->export();
});

it('exports included files', function () {
    app(Exporter::class)
        ->includeFiles([__DIR__ . '/stubs/public' => ''])
        ->export();

    assertFileExists(__DIR__ . '/dist/favicon.ico');
    assertFileExists(__DIR__ . '/dist/media/image.png');

    expect(file_exists(__DIR__ . '/dist/index.php'))->toBeFalse();
});
