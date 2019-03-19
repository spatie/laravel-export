<?php

return [

    /*
     * Files will be saved to this disk. Disks can be configured in
     * `config/filesystems.php`.
     */
    'disk' => 'export',

    /*
     * The entry points of your app. The export crawler will start to build
     * pages from these URL's.
     */
    'entries' => [
        env('APP_URL'),
    ],

    /*
     * Files that should be included in the build.
     */
    'include' => [
        ['source' => 'public', 'target' => ''],
    ],

    /*
     * Files that should be excluded from the build.
     */
    'exclude' => [
        '.php$',
    ],

    /*
     * Shell commands that should be run before the export will be created.
     */
    'before' => [
        // '/usr/local/bin/yarn production',
    ],

    /*
     * Shell commands that should be run after the export was created.
     */
    'after' => [
        // '/usr/local/bin/netlify deploy --prod',
    ],

];
