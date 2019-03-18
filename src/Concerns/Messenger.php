<?php

namespace Spatie\Export\Concerns;

trait Messenger
{
    /** @var callable|null */
    protected $onMessage;

    public function onMessage(callable $onMessage): void
    {
        $this->onMessage = $onMessage;
    }

    public function message(string $message): void
    {
        if ($this->onMessage) {
            ($this->onMessage)($message);
        }
    }
}
