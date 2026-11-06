<?php

declare(strict_types=1);

namespace Interface\Syntax;

/** One word on the line, with where it sat and what the tagger decided. */
final class Token
{
    /**
     * @param array<string, string> $features the morphology this instance carries
     */
    public function __construct(
        public readonly string $text,
        public readonly int $index,
        public readonly string $wordClass,
        public readonly array $features = [],
    ) {
    }

    public function feature(string $name): ?string
    {
        return $this->features[$name] ?? null;
    }

    public function is(string $wordClass): bool
    {
        return strtolower($wordClass) === $this->wordClass;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'index' => $this->index,
            'class' => $this->wordClass,
            'features' => $this->features,
        ];
    }
}
