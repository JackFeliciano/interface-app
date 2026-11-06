<?php

declare(strict_types=1);

namespace Interface\Lexis;

/**
 * The eleven families a record's part of speech comes from. They are coarser
 * than the sixteen teaching classes: a lemma is filed once and the classes are
 * read off the form it takes.
 */
final class PartOfSpeech
{
    public const NOUN = 'noun';
    public const VERB = 'verb';
    public const ADJECTIVE = 'adjective';
    public const ADVERB = 'adverb';
    public const PRONOUN = 'pronoun';
    public const DETERMINER = 'determiner';
    public const PREPOSITION = 'preposition';
    public const CONJUNCTION = 'conjunction';
    public const NUMERAL = 'numeral';
    public const INTERJECTION = 'interjection';
    public const PARTICLE = 'particle';

    /** @var array<string, list<string>> family => the teaching classes it covers */
    private const COVERS = [
        self::NOUN => ['noun', 'participle', 'gerund'],
        self::VERB => ['verb', 'infinitive', 'auxiliary'],
        self::ADJECTIVE => ['adjective', 'participle'],
        self::ADVERB => ['adverb', 'particle'],
        self::PRONOUN => ['pronoun', 'clitic'],
        self::DETERMINER => ['article', 'numeral'],
        self::PREPOSITION => ['preposition'],
        self::CONJUNCTION => ['conjunction'],
        self::NUMERAL => ['numeral'],
        self::INTERJECTION => ['interjection'],
        self::PARTICLE => ['particle', 'clitic'],
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::COVERS);
    }

    public static function knows(string $name): bool
    {
        return isset(self::COVERS[strtolower($name)]);
    }

    /** @return list<string> */
    public static function covers(string $name): array
    {
        return self::COVERS[strtolower($name)] ?? [];
    }

    /** The family a teaching class belongs to, or null when none claims it. */
    public static function of(string $wordClass): ?string
    {
        foreach (self::COVERS as $family => $classes) {
            if (in_array(strtolower($wordClass), $classes, true)) {
                return $family;
            }
        }

        return null;
    }
}
