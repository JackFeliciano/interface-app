<?php

declare(strict_types=1);

namespace Interface\Lexis;

/**
 * A concept with its lemmas. The record stores no inflected form: the engine
 * derives them, so a verb yields every form it takes and a regular noun nobody
 * entered still declines.
 *
 * Each record carries a part of speech from the eleven families, a semantic
 * group, the morphological family, and one sense definition.
 */
final class Lemma
{
    /**
     * @param array<string, string> $words language => the lemma in that language
     * @param list<Sense> $senses
     * @param array<string, string> $extra whatever else the record must carry
     */
    public function __construct(
        public readonly string $concept,
        public readonly string $partOfSpeech,
        public readonly string $semanticGroup,
        public readonly string $morphologicalFamily,
        public readonly array $words,
        public readonly array $senses,
        public readonly string $definition,
        public readonly array $extra = [],
    ) {
    }

    public function lemma(string $language): ?string
    {
        return $this->words[strtolower($language)] ?? null;
    }

    /** @return list<string> */
    public function languages(): array
    {
        return array_keys($this->words);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'concept' => $this->concept,
            'partOfSpeech' => $this->partOfSpeech,
            'semanticGroup' => $this->semanticGroup,
            'morphologicalFamily' => $this->morphologicalFamily,
            'words' => $this->words,
            'senses' => array_map(static fn (Sense $s): array => $s->toArray(), $this->senses),
            'definition' => $this->definition,
            'extra' => $this->extra,
        ];
    }
}
