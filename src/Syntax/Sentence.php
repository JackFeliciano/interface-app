<?php

declare(strict_types=1);

namespace Interface\Syntax;

/** A parsed sentence: one of five schemes, its cores, and the adjuncts orbiting them. */
final class Sentence
{
    /**
     * @param list<Token> $tokens
     * @param list<array{role: string, start: int, end: int}> $cores
     * @param list<array{role: string, start: int, end: int}> $adjuncts
     */
    public function __construct(
        public readonly string $text,
        public readonly string $scheme,
        public readonly array $tokens,
        public readonly array $cores,
        public readonly array $adjuncts,
    ) {
    }

    /** The words the sentence cannot do without, in reading order. */
    public function coreText(): string
    {
        return $this->join($this->cores);
    }

    /** What a learner can drop and still hold a sentence. */
    public function adjunctText(): string
    {
        return $this->join($this->adjuncts);
    }

    /** @param list<array{role: string, start: int, end: int}> $parts */
    private function join(array $parts): string
    {
        $words = [];
        foreach ($parts as $part) {
            for ($i = $part['start']; $i <= $part['end']; $i++) {
                $words[] = $this->tokens[$i]->text;
            }
        }

        return implode(' ', $words);
    }

    /**
     * The sentence with only its cores, which is the shape a learner has to
     * produce before any adjunct earns a place.
     */
    public function skeleton(): string
    {
        return $this->coreText();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'scheme' => $this->scheme,
            'cores' => $this->cores,
            'adjuncts' => $this->adjuncts,
            'coreText' => $this->coreText(),
            'adjunctText' => $this->adjunctText(),
            'tokens' => array_map(static fn (Token $t): array => $t->toArray(), $this->tokens),
        ];
    }
}
