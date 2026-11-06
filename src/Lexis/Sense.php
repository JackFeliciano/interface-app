<?php

declare(strict_types=1);

namespace Interface\Lexis;

/**
 * One sense of a concept, in one language. English leads and Portuguese and
 * Russian sit beside it to mark the senses English blurs: a Russian verb carries
 * its aspect, and a Portuguese verb chooses between the two past tenses.
 */
final class Sense
{
    /**
     * @param list<string> $marks the distinctions this language makes and English does not
     */
    public function __construct(
        public readonly string $language,
        public readonly string $gloss,
        public readonly array $marks = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'language' => $this->language,
            'gloss' => $this->gloss,
            'marks' => $this->marks,
        ];
    }
}
