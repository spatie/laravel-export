<?php

namespace Spatie\Export;

interface RequestHandler
{
    public function send(string $url): string;
}
