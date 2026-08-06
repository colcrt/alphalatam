<?php

declare(strict_types=1);

namespace App\Database;

class RawExpression
{
    public function __construct(
        public readonly string $expression,
        public readonly array $bindings = []
    ) {}

    public function __toString(): string
    {
        return $this->expression;
    }
}
