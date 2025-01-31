<?php

use Illuminate\Support\Facades\Storage;
use Spatie\Export\Destinations\FilesystemDestination;
use Spatie\Export\Jobs\IncludeFile;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function Illuminate\Filesystem\join_paths;
use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFileExists;

function IF_test_here(string $path = '') {
    return join_paths(dirname(__DIR__), $path);
}

function IF_test_dist(string $path = '') {
    return join_paths(IF_test_here('dist'), $path);
}

function IF_test_stubs(string $path = '') {
    return join_paths(IF_test_here('stubs'), $path);
}

function IF_test_assertExportedFile(string $path, string $content): void
{
    assertFileExists($path);
    assertEquals($content, file_get_contents($path));
}

function IF_test_run_cmd(/*int|Command*/ $cmd): int {
    return is_object($cmd) ? $cmd->run() : $cmd;
}

function IF_test_storage_link() {
    $opts = ['--relative' => false];

    $cmd = artisan('storage:link', $opts);

    $exitCode = IF_test_run_cmd($cmd);
    expect($exitCode)->toBe(0);
}

function IF_test_storage_unlink() {
    $cmd = artisan('storage:unlink');

    $exitCode = IF_test_run_cmd($cmd);
    expect($exitCode)->toBe(0);
}

describe(IncludeFile::class, function () {
    // Reusable routine to test on various platforms with different
    // assertions on the symlink that is being inspected

    /**
     * @param callable(SplFileInfo): void $assertions
     */
    $symlinkTest = function (callable $assertions) {
        $config = config();

        // We keep track of the config items we modify in order
        // to be able to restore them after the test has run
        $oldConfig = [];
        $oldConfig['filesystems.links'] = $config->get('filesystems.links');
        $oldConfig['filesystems.disks.laravel_export'] = $config->get('filesystems.disks.laravel_export');

        // We're using /tests/stubs/storage/app/public/ as the content
        // directory being symlink'd through `php artisan storage:link`
        $srcPath = join_paths(IF_test_stubs('storage'), 'app', 'public');

        // We're using /tests/stubs/public/public-symlink as the
        // symlink's pathname to track it around
        $linkPath = join_paths(IF_test_stubs('public'), 'public-symlink');

        // We'll be referring to this file to assert that the directory
        // has been properly copied through its symlink
        $fileToCheck = 'test.txt';

        try {
            // First we make sure our directory link mapping
            // has been set in the config for the command
            // to find when we'll run it
            $config->set('filesystems.links', [
                $linkPath => $srcPath,
            ]);

            // We also configure a storage disk for the output
            // that follows the same structure as other tests.
            // It allows us to easily inject the instances
            // of the parameters required for the
            // IncludeFile constructor
            $config->set('filesystems.disks.laravel_export', [
                'driver' => 'local',
                'root' => IF_test_dist(),
            ]);


            // We execute `php artisan storage:link` to create our
            // test symlink object. This has been chosen as it's
            // very likely to be the one people would have
            // issues with when using this package.
            IF_test_storage_link();

            // We get an SplFileInfo instance of our symlink to
            // be able to write assertions about. We use the
            // finder directly as we can't portably get it
            // with a single method call across platforms.
            $finder = Finder::create()->in(dirname($linkPath))->ignoreDotFiles(true);
            $bn = basename($linkPath);
            $splInfo = collect($finder->files())
                ->concat($finder->directories())
                ->filter(fn ($splInfo) => $splInfo->getBasename() == $bn)
                ->first();

            // This is where we plug in the test assertions
            // to ensure that the symlink has the expected
            // properties on our target platform.
            // Only Windows behaves differently so there will
            // only be two tests about it
            $assertions($splInfo);

            $instance = new IncludeFile(IF_test_stubs('public'), '', [
                '/\.php$/',
                '/mix-manifest\.json$/',
            ]);

            $fs = Storage::disk('laravel_export');
            $destination = new FilesystemDestination($fs);
            $instance->handle($destination);

            // We assert that the contents in the exported file
            // matches with the contents from the original.
            IF_test_assertExportedFile(
                join_paths(IF_test_dist('public-symlink'), $fileToCheck),
                file_get_contents(join_paths($srcPath, $fileToCheck))
            );

            $exportedSplInfo = collect(
                Finder::create()
                ->in(IF_test_dist('public-symlink'))
                ->files()
            )->filter(fn ($splInfo) => $splInfo->getBasename() == $fileToCheck)
            ->first();

            $srcSplInfo = collect(
                Finder::create()
                ->in($srcPath)
                ->files()
            )->filter(fn ($splInfo) => $splInfo->getBasename() == $fileToCheck)
            ->first();

            // We compare the real paths behind the source and
            // exported file as to ensure that we
            // actually copied the files and
            // not just copy the symlink.
            expect($exportedSplInfo->getRealPath())->not->toBe($srcSplInfo->getRealPath());
        } finally {
            $config->set('filesystems.links', $oldConfig['filesystems.links']);
            $config->set('filesystems.disks.laravel_export', $oldConfig['filesystems.disks.laravel_export']);
        
            IF_test_storage_unlink();
        }
    };

    it('properly copies the contents of a Windows-style symlink to a directory', function () use($symlinkTest) {
        $symlinkTest(function (SplFileInfo $splInfo) {
            // Checking isDir() and isLink() on $item could lead to false negatives due to the way
            // symlinks are handled on Windows, especially if created under Git-For-Windows.
            // When debugging it could lead to isDir(), isFile() and isLink() to all 3 be false.
            // Despite that, getRealPath() does return the resolved symlink path.
            // Hence why we check on $realItem instead.
            // Copying Windows "symlinks" as files would also be "wrong" and not portable across file systems.

            expect($splInfo->isDir())->toBeFalse();
            expect($splInfo->isFile())->toBeFalse();
            expect($splInfo->isLink())->toBeFalse();
        });
    })->onlyOnWindows();

    it('properly copies the contents of a symlink to a directory', function () use($symlinkTest) {
        $symlinkTest(function (SplFileInfo $splInfo) {
            // On a UNIX / non-Windows system, a symlink to a directory
            // leads to it being detected both as a directory
            // and a symlink when using SplFileInfo. 

            expect($splInfo->isDir())->toBeTrue();
            expect($splInfo->isFile())->toBeFalse();
            expect($splInfo->isLink())->toBeTrue();
        });
    })->skipOnWindows();
});
