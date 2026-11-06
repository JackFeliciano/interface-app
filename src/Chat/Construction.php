<?php

declare(strict_types=1);

namespace Interface\Chat;

/**
 * A construction is what a language does on its own terms, and the learner meets
 * it as it stands instead of translating it word for word.
 *
 * The clearest family is the one where the thing liked stands as the subject and
 * the person carries the dative: the Italian mi piace and the Spanish me gusta.
 * The learner who says ho fame and says it right has learned that hunger is a
 * thing a person has, not a thing a person is.
 */
final class Construction
{
    /**
     * @param list<string> $languages the languages that do it this way
     * @param list<string> $examples literal instances, the language named in front
     * @param string $trap what a word-for-word translation gets wrong
     */
    public function __construct(
        public readonly string $name,
        public readonly string $family,
        public readonly array $languages,
        public readonly string $pattern,
        public readonly array $examples,
        public readonly string $trap,
        public readonly string $explanation,
    ) {
    }

    public function speaks(string $language): bool
    {
        return in_array(strtolower($language), array_map('strtolower', $this->languages), true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'family' => $this->family,
            'languages' => $this->languages,
            'pattern' => $this->pattern,
            'examples' => $this->examples,
            'trap' => $this->trap,
            'explanation' => $this->explanation,
        ];
    }
}
