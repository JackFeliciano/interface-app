<?php

declare(strict_types=1);

namespace Interface\Syntax;

/**
 * The five schemes a sentence can take. The names read from the simplest to the
 * most loaded, and every sentence the engine accepts lands in exactly one.
 */
final class Scheme
{
    /** Subject, a linking verb, and a predicate that says what the subject is. */
    public const SUBJECT_PREDICATE = 'subject-predicate';
    /** Subject and an intransitive verb, with nothing to receive the action. */
    public const SUBJECT_VERB = 'subject-verb';
    /** Subject, a transitive verb, and the object it acts on. */
    public const SUBJECT_VERB_OBJECT = 'subject-verb-object';
    /** Subject, a verb, an object, and a second object that receives the thing. */
    public const SUBJECT_VERB_OBJECT_OBJECT = 'subject-verb-object-object';
    /** Subject, a verb, an object, and a prepositional object after it. */
    public const SUBJECT_VERB_PREPOSITIONAL = 'subject-verb-prepositional';

    /** @var array<string, string> scheme => the formula a learner reads */
    private const FORMULAS = [
        self::SUBJECT_PREDICATE => 'S + LV + P',
        self::SUBJECT_VERB => 'S + V',
        self::SUBJECT_VERB_OBJECT => 'S + V + O',
        self::SUBJECT_VERB_OBJECT_OBJECT => 'S + V + O + O',
        self::SUBJECT_VERB_PREPOSITIONAL => 'S + V + O + PO',
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::FORMULAS);
    }

    public static function formula(string $scheme): string
    {
        return self::FORMULAS[$scheme] ?? '';
    }

    public static function knows(string $scheme): bool
    {
        return isset(self::FORMULAS[$scheme]);
    }

    /** The cores each scheme requires, in order. @return list<string> */
    public static function slots(string $scheme): array
    {
        return match ($scheme) {
            self::SUBJECT_PREDICATE => ['subject', 'predicate'],
            self::SUBJECT_VERB => ['subject', 'verb'],
            self::SUBJECT_VERB_OBJECT => ['subject', 'verb', 'object'],
            self::SUBJECT_VERB_OBJECT_OBJECT => ['subject', 'verb', 'object', 'object2'],
            self::SUBJECT_VERB_PREPOSITIONAL => ['subject', 'verb', 'object', 'prepositional'],
            default => [],
        };
    }
}
