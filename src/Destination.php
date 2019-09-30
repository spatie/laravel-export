<?php

namespace Spatie\Export;

interface Destination
{
    public function clean();

    public function write(string $path, string $contents);
}
