<?php

declare(strict_types=1);

namespace Interface\Syntax;

/**
 * The words the tagger knows, each with its class and, where one word can be
 * more than one class, every class it can take. A word the lexicon has never
 * seen comes back as a noun guess, and the parse says so in its notes.
 */
final class Lexicon
{
    /** @var array<string, array{class: string, also?: list<string>, features?: array<string, string>}> */
    private array $entries = [];

    /** @param array<string, array<string, mixed>> $entries */
    public static function fromArray(array $entries): self
    {
        $lex = new self();
        foreach ($entries as $word => $spec) {
            if (!isset($spec['class'])) {
                throw new \InvalidArgumentException('lexicon entry for ' . $word . ' has no class');
            }
            $lex->entries[strtolower($word)] = [
                'class' => $spec['class'],
                'also' => $spec['also'] ?? [],
                'features' => $spec['features'] ?? [],
            ];
        }

        return $lex;
    }

    public static function english(): self
    {
        return self::fromArray(self::englishTable());
    }

    public function has(string $word): bool
    {
        return isset($this->entries[strtolower($word)]);
    }

    /** @return list<string> every class this word can take, primary first */
    public function classes(string $word): array
    {
        $key = strtolower($word);
        if (!isset($this->entries[$key])) {
            return [];
        }

        return array_values(array_unique(array_merge(
            [$this->entries[$key]['class']],
            $this->entries[$key]['also'],
        )));
    }

    /** The class the tagger picks before context narrows it. */
    public function primary(string $word): ?string
    {
        return $this->entries[strtolower($word)]['class'] ?? null;
    }

    /** @return array<string, string> */
    public function features(string $word): array
    {
        return $this->entries[strtolower($word)]['features'] ?? [];
    }

    public function size(): int
    {
        return count($this->entries);
    }

    /** @return array<string, array<string, mixed>> */
    private static function englishTable(): array
    {
        $t = [];

        // Determiners and pronouns.
        foreach (['the'] as $w) {
            $t[$w] = ['class' => WordClass::ARTICLE, 'features' => ['number' => 'both']];
        }
        foreach (['a', 'an'] as $w) {
            $t[$w] = ['class' => WordClass::ARTICLE, 'features' => ['number' => 'singular']];
        }
        foreach (['i', 'you', 'he', 'she', 'it', 'we', 'they'] as $w) {
            $t[$w] = ['class' => WordClass::PRONOUN, 'features' => ['person' => 'subject']];
        }
        foreach (['me', 'him', 'her', 'us', 'them'] as $w) {
            $t[$w] = ['class' => WordClass::PRONOUN, 'features' => ['person' => 'object']];
        }
        foreach (['my', 'your', 'his', 'our', 'their', 'its'] as $w) {
            $t[$w] = ['class' => WordClass::PRONOUN, 'features' => ['person' => 'possessive']];
        }
        foreach (['this', 'that', 'these', 'those'] as $w) {
            $t[$w] = ['class' => WordClass::PRONOUN, 'also' => [WordClass::ARTICLE]];
        }

        // The copula and the auxiliaries. A linking verb is a verb whose complement
        // is a predicate, and the engine marks it separately below.
        foreach (['is', 'are', 'was', 'were', 'be', 'been', 'am'] as $w) {
            $t[$w] = ['class' => WordClass::VERB, 'also' => [WordClass::AUXILIARY], 'features' => ['linking' => 'yes']];
        }
        foreach (['seem', 'seems', 'seemed', 'become', 'becomes', 'became', 'remain', 'remains', 'look', 'looks', 'feel', 'feels'] as $w) {
            $t[$w] = ['class' => WordClass::VERB, 'features' => ['linking' => 'yes']];
        }
        foreach (['has', 'have', 'had', 'do', 'does', 'did', 'will', 'would', 'can', 'could', 'may', 'might', 'must', 'shall', 'should'] as $w) {
            $t[$w] = ['class' => WordClass::AUXILIARY, 'also' => [WordClass::VERB]];
        }

        // Lexical verbs, base and third person, and a regular past in -ed.
        $verbs = [
            'read', 'reads', 'write', 'writes', 'run', 'runs', 'eat', 'eats', 'drink', 'drinks',
            'see', 'sees', 'make', 'makes', 'take', 'takes', 'give', 'gives', 'send', 'sends',
            'buy', 'buys', 'sell', 'sells', 'build', 'builds', 'open', 'opens', 'close', 'closes',
            'study', 'studies', 'learn', 'learns', 'teach', 'teaches', 'speak', 'speaks',
            'live', 'lives', 'work', 'works', 'walk', 'walks', 'arrive', 'arrives',
            'sleep', 'sleeps', 'wait', 'waits', 'listen', 'listens', 'answer', 'answers',
            'put', 'puts', 'bring', 'brings', 'find', 'finds', 'keep', 'keeps', 'leave', 'leaves',
        ];
        foreach ($verbs as $w) {
            $t[$w] = ['class' => WordClass::VERB];
        }
        // A verb that can also be a noun keeps the noun reading, and the tagger
        // picks the verb when a subject pronoun stands in front of it.
        foreach (['read', 'walk', 'work', 'wait', 'answer', 'open', 'close', 'study', 'book', 'run', 'sleep', 'talk', 'buy', 'sell', 'look'] as $w) {
            if (isset($t[$w])) {
                $t[$w]['also'] = [WordClass::NOUN];
            }
        }
        // The verbs that take two objects, and the ones that take a prepositional one.
        foreach (['give', 'gives', 'gave', 'send', 'sends', 'sent', 'tell', 'tells', 'told', 'show', 'shows', 'showed', 'bring', 'brings', 'brought', 'offer', 'offers', 'teach', 'teaches', 'taught', 'lend', 'lends', 'hand', 'hands'] as $w) {
            $t[$w] = ['class' => WordClass::VERB, 'features' => ['valence' => 'dative']];
        }
        foreach (['listen', 'listens', 'wait', 'waits', 'look', 'looks', 'talk', 'talks', 'arrive', 'arrives', 'belong', 'belongs', 'depend', 'depends'] as $w) {
            $t[$w] = ['class' => WordClass::VERB, 'features' => ['valence' => 'prepositional']];
        }

        // Nouns, the largest open class and the tagger's fallback.
        $nouns = [
            'book', 'books', 'house', 'houses', 'letter', 'letters', 'coffee', 'tea', 'water',
            'city', 'cities', 'man', 'men', 'woman', 'women', 'child', 'children', 'dog', 'dogs',
            'cat', 'cats', 'table', 'tables', 'door', 'doors', 'window', 'windows', 'car', 'cars',
            'train', 'trains', 'student', 'students', 'teacher', 'teachers', 'friend', 'friends',
            'language', 'languages', 'sentence', 'sentences', 'word', 'words', 'grammar',
            'morning', 'evening', 'night', 'day', 'days', 'year', 'years', 'time',
            'garden', 'gardens', 'music', 'song', 'songs', 'problem', 'problems', 'question',
            'questions', 'answer', 'answers', 'market', 'school', 'schools', 'office',
        ];
        foreach ($nouns as $w) {
            $t[$w] = ['class' => WordClass::NOUN];
        }

        // Adjectives.
        foreach (['big', 'small', 'good', 'bad', 'new', 'old', 'young', 'long', 'short', 'happy', 'sad', 'easy', 'hard', 'quiet', 'loud', 'red', 'blue', 'green'] as $w) {
            $t[$w] = ['class' => WordClass::ADJECTIVE];
        }

        // Adverbs.
        foreach (['very', 'quickly', 'slowly', 'often', 'always', 'never', 'today', 'yesterday', 'tomorrow', 'here', 'there', 'now', 'then', 'again', 'already', 'still'] as $w) {
            $t[$w] = ['class' => WordClass::ADVERB];
        }

        // Prepositions, the adjunct's usual opening word.
        foreach (['in', 'on', 'at', 'to', 'from', 'with', 'without', 'by', 'for', 'of', 'under', 'over', 'after', 'before', 'during', 'between', 'into', 'about'] as $w) {
            $t[$w] = ['class' => WordClass::PREPOSITION];
        }

        // Conjunctions, which decide compound and complex.
        foreach (['and', 'or', 'but', 'nor', 'yet'] as $w) {
            $t[$w] = ['class' => WordClass::CONJUNCTION, 'features' => ['kind' => 'coordinating']];
        }
        foreach (['because', 'although', 'if', 'when', 'while', 'since', 'unless', 'that', 'which', 'who'] as $w) {
            $t[$w] = ['class' => WordClass::CONJUNCTION, 'features' => ['kind' => 'subordinating']];
        }

        // Negation and the particles that ride a verb.
        foreach (['not', 'to'] as $w) {
            $t[$w] = ['class' => WordClass::PARTICLE];
        }

        return $t;
    }
}
