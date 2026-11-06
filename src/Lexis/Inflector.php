<?php

declare(strict_types=1);

namespace Interface\Lexis;

/**
 * Derives the forms of a lemma. The tables are English's, and they are regular
 * by design: an irregular form is stored on the record under extra.forms, and a
 * regular noun that nobody entered still declines.
 */
final class Inflector
{
    /** @return list<string> every form the lemma takes, the lemma itself first */
    public function forms(Lemma $lemma, string $language = 'en'): array
    {
        $word = $lemma->lemma($language);
        if ($word === null) {
            return [];
        }

        if ($language !== 'en') {
            // The derivation tables are English's. Other languages answer with
            // the stored forms when a record carries them, and with the lemma.
            $stored = $lemma->extra['forms_' . $language] ?? [];

            return array_values(array_unique(array_merge([$word], $stored)));
        }

        $stored = $lemma->extra['forms'] ?? [];
        if ($stored !== []) {
            return array_values(array_unique(array_merge([$word], $stored)));
        }

        return match ($lemma->partOfSpeech) {
            PartOfSpeech::VERB => $this->verb($word),
            PartOfSpeech::NOUN => $this->noun($word),
            PartOfSpeech::ADJECTIVE => $this->adjective($word),
            default => [$word],
        };
    }

    /** @return list<string> */
    private function verb(string $word): array
    {
        $third = $this->thirdPerson($word);
        $past = $this->past($word);
        $participle = $past;
        $gerund = $this->gerund($word);

        return array_values(array_unique([$word, $third, $past, $participle, $gerund]));
    }

    /** @return list<string> */
    private function noun(string $word): array
    {
        return array_values(array_unique([$word, $this->plural($word)]));
    }

    /** @return list<string> */
    private function adjective(string $word): array
    {
        $forms = [$word];
        if (strlen($word) > 2) {
            $forms[] = $word . 'er';
            $forms[] = $word . 'est';
        }

        return $forms;
    }

    public function plural(string $word): string
    {
        if ($word === '') {
            return '';
        }
        if ($this->endsWith($word, ['s', 'x', 'z', 'ch', 'sh'])) {
            return $word . 'es';
        }
        if ($this->endsWith($word, ['y']) && !$this->isVowel($word[strlen($word) - 2] ?? 'a')) {
            return substr($word, 0, -1) . 'ies';
        }

        return $word . 's';
    }

    private function thirdPerson(string $word): string
    {
        if ($this->endsWith($word, ['s', 'x', 'z', 'ch', 'sh', 'o'])) {
            return $word . 'es';
        }
        if ($this->endsWith($word, ['y']) && !$this->isVowel($word[strlen($word) - 2] ?? 'a')) {
            return substr($word, 0, -1) . 'ies';
        }

        return $word . 's';
    }

    private function past(string $word): string
    {
        if ($this->endsWith($word, ['e'])) {
            return $word . 'd';
        }
        if ($this->endsWith($word, ['y']) && !$this->isVowel($word[strlen($word) - 2] ?? 'a')) {
            return substr($word, 0, -1) . 'ied';
        }

        return $word . 'ed';
    }

    private function gerund(string $word): string
    {
        if ($this->endsWith($word, ['e']) && !$this->endsWith($word, ['ee', 'ye'])) {
            return substr($word, 0, -1) . 'ing';
        }

        return $word . 'ing';
    }

    /** @param list<string> $suffixes */
    private function endsWith(string $word, array $suffixes): bool
    {
        foreach ($suffixes as $suffix) {
            if (str_ends_with(strtolower($word), $suffix)) {
                return true;
            }
        }

        return false;
    }

    private function isVowel(string $letter): bool
    {
        return in_array(strtolower($letter), ['a', 'e', 'i', 'o', 'u'], true);
    }
}
