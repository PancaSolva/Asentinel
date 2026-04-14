<?php

namespace App\Health;

final class CheckResult
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $healthy,
        public readonly array $context = [],
    ) {
    }

    public function status(): string
    {
        return $this->healthy ? 'up' : 'down';
    }
}
