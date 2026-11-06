<?php

declare(strict_types=1);

namespace Interface\Syntax;

/** A sentence the engine could not place, carrying the word that stopped it. */
final class ParseError extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $position = 0,
        public readonly string $word = '',
    ) {
        parent::__construct($message);
    }
}
