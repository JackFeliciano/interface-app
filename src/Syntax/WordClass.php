<?php

declare(strict_types=1);

namespace Interface\Syntax;

/**
 * The sixteen word classes. A learner meets them in his own language first, so
 * the same sixteen labels sit under every Indo-European language he takes.
 *
 * Each class carries the morphology it inflects for. A class with no slots is
 * invariant, and the engine says so rather than inventing a paradigm.
 */
final class WordClass
{
    public const NOUN = 'noun';
    public const PRONOUN = 'pronoun';
    public const ARTICLE = 'article';
    public const ADJECTIVE = 'adjective';
    public const NUMERAL = 'numeral';
    public const VERB = 'verb';
    public const ADVERB = 'adverb';
    public const PREPOSITION = 'preposition';
    public const CONJUNCTION = 'conjunction';
    public const INTERJECTION = 'interjection';
    public const PARTICIPLE = 'participle';
    public const INFINITIVE = 'infinitive';
    public const GERUND = 'gerund';
    public const AUXILIARY = 'auxiliary';
    public const CLITIC = 'clitic';
    public const PARTICLE = 'particle';

    /** @var array<string, list<string>> class name => the slots it inflects for */
    private const SLOTS = [
        self::NOUN => ['number', 'case'],
        self::PRONOUN => ['person', 'number', 'case', 'gender'],
        self::ARTICLE => ['number', 'case', 'gender'],
        self::ADJECTIVE => ['number', 'case', 'gender', 'degree'],
        self::NUMERAL => ['number', 'case', 'gender'],
        self::VERB => ['person', 'number', 'tense', 'mood', 'aspect', 'voice'],
        self::ADVERB => ['degree'],
        self::PREPOSITION => [],
        self::CONJUNCTION => [],
        self::INTERJECTION => [],
        self::PARTICIPLE => ['number', 'case', 'gender', 'tense', 'voice'],
        self::INFINITIVE => ['tense', 'voice'],
        self::GERUND => ['case'],
        self::AUXILIARY => ['person', 'number', 'tense', 'mood'],
        self::CLITIC => ['person', 'number', 'case'],
        self::PARTICLE => [],
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::SLOTS);
    }

    public static function knows(string $name): bool
    {
        return isset(self::SLOTS[strtolower($name)]);
    }

    /** @return list<string> */
    public static function slots(string $name): array
    {
        return self::SLOTS[strtolower($name)] ?? [];
    }

    public static function isInvariant(string $name): bool
    {
        return self::knows($name) && self::SLOTS[strtolower($name)] === [];
    }

    /** @return list<string> every class that inflects for person and number */
    public static function personal(): array
    {
        $out = [];
        foreach (self::SLOTS as $name => $slots) {
            if (in_array('person', $slots, true) && in_array('number', $slots, true)) {
                $out[] = $name;
            }
        }

        return $out;
    }
}
