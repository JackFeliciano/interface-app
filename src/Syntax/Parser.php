<?php

declare(strict_types=1);

namespace Interface\Syntax;

/**
 * Sorts a sentence into one of five schemes and separates the cores from the
 * adjuncts that orbit them.
 *
 * The method is the one a learner is taught. Find the verb. Ask who or what does
 * it, which is the subject. Then ask what the verb needs to make sense, and what
 * merely adds to it. Anything opening with a preposition is an adjunct unless a
 * verb in this sentence asks for a prepositional object, and the engine decides
 * that from the verb's own entry.
 */
final class Parser
{
    private Tagger $tagger;

    /** @var list<string> adjunct roles for this parse */
    private const ADJUNCT_ROLES = ['time', 'place', 'manner', 'reason', 'condition', 'accompaniment'];

    public function __construct(private readonly Lexicon $lexicon)
    {
        $this->tagger = new Tagger($lexicon);
    }

    public function parse(string $text): Sentence
    {
        $tokens = $this->tagger->tag($text);
        if ($tokens === []) {
            throw new ParseError('nothing to parse', 0, '');
        }

        $phrase = $this->phrase($tokens);
        if ($phrase === []) {
            throw new ParseError('nothing to parse', 0, '');
        }

        $verbAt = $this->findVerb($phrase);
        if ($verbAt < 0) {
            throw new ParseError('no verb in the sentence', $phrase[0]->index, $phrase[0]->text);
        }

        $verb = $phrase[$verbAt];
        $subject = $this->subject($phrase, $verbAt);
        $rest = array_slice($phrase, $verbAt + 1);

        $cores = [];
        $adjuncts = [];

        if ($rest === []) {
            $scheme = Scheme::SUBJECT_VERB;
            $cores[] = ['role' => 'subject', 'start' => $subject['start'], 'end' => $subject['end']];
            $cores[] = ['role' => 'verb', 'start' => $verbAt, 'end' => $verbAt];
        } else {
            $split = $this->split($rest, $verbAt + 1, $verb);
            $cores = $split['cores'];
            $adjuncts = $split['adjuncts'];
            $scheme = $this->scheme($verb, $cores);

            array_unshift($cores, ['role' => 'subject', 'start' => $subject['start'], 'end' => $subject['end']]);
            array_splice($cores, 1, 0, [['role' => 'verb', 'start' => $verbAt, 'end' => $verbAt]]);

            $cores = $this->reorder($cores);

            if ($verb->feature('linking') === 'yes') {
                foreach ($cores as $k => $core) {
                    if ($core['role'] === 'object' || $core['role'] === 'object2') {
                        $cores[$k]['role'] = 'predicate';
                    }
                }
            }
        }

        return new Sentence($text, $scheme, $tokens, $cores, $adjuncts);
    }

    /** Which word a learner would call the verb, and where a clause boundary sits. */
    public function verbOf(string $text): ?Token
    {
        $tokens = $this->tagger->tag($text);
        $phrase = $this->phrase($tokens);
        $at = $this->findVerb($phrase);

        return $at < 0 ? null : $phrase[$at];
    }

    /** @return list<string> */
    public function notes(): array
    {
        return $this->tagger->notes();
    }

    /**
     * Drops a leading coordinating conjunction and stops at a subordinating one,
     * so a compound or a complex sentence parses as its head clause.
     *
     * @param list<Token> $tokens
     * @return list<Token>
     */
    private function phrase(array $tokens): array
    {
        $out = [];
        foreach ($tokens as $token) {
            if ($token->is(WordClass::CONJUNCTION)) {
                $kind = $token->feature('kind');
                if ($out === []) {
                    continue;
                }
                if ($kind === 'coordinating') {
                    break;
                }
                break;
            }
            if ($token->is(WordClass::PARTICLE) && $token->text === 'not') {
                $out[] = $token;
                continue;
            }
            $out[] = $token;
        }

        return $out;
    }

    /** @param list<Token> $phrase */
    private function findVerb(array $phrase): int
    {
        foreach ($phrase as $i => $token) {
            if ($token->is(WordClass::VERB) || $token->is(WordClass::AUXILIARY)) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * The subject is everything from the start to the verb. A pronoun is the
     * whole subject. A noun phrase takes its determiner and adjectives with it.
     *
     * @param list<Token> $phrase
     * @return array{start: int, end: int}
     */
    private function subject(array $phrase, int $verbAt): array
    {
        $start = 0;
        for ($i = 0; $i < $verbAt; $i++) {
            if (!$phrase[$i]->is(WordClass::PARTICLE)) {
                $start = $i;
                break;
            }
        }

        return ['start' => $start, 'end' => max($start, $verbAt - 1)];
    }

    /**
     * Walks what follows the verb and sorts each stretch into a core or an
     * adjunct.
     *
     * @param list<Token> $rest
     * @return array{cores: list<array{role: string, start: int, end: int}>, adjuncts: list<array{role: string, start: int, end: int}>}
     */
    private function split(array $rest, int $offset, Token $verb): array
    {
        $cores = [];
        $adjuncts = [];
        $i = 0;
        $count = count($rest);

        $dative = $this->verbTakesDative($verb);
        $prepositional = $this->verbTakesPrepositional($verb);
        $nounSeen = 0;

        while ($i < $count) {
            $token = $rest[$i];

            if ($token->is(WordClass::PREPOSITION)) {
                $stop = $i;
                while ($stop + 1 < $count && !$rest[$stop + 1]->is(WordClass::PREPOSITION)) {
                    $stop++;
                }

                $span = ['start' => $offset + $i, 'end' => $offset + $stop];

                // A verb that asks for a prepositional object keeps it as a core.
                if ($prepositional && $nounSeen === 0) {
                    $cores[] = ['role' => 'prepositional', 'start' => $span['start'], 'end' => $span['end']];
                    $nounSeen++;
                } else {
                    $adjuncts[] = ['role' => $this->prepositionRole($token->text), 'start' => $span['start'], 'end' => $span['end']];
                }
                $i = $stop + 1;
                continue;
            }

            if ($token->is(WordClass::ADVERB)) {
                $adjuncts[] = ['role' => 'manner', 'start' => $offset + $i, 'end' => $offset + $i];
                $i++;
                continue;
            }

            // A noun phrase runs until a preposition, an adverb or the end, and a
            // determiner opens a new phrase even when no preposition separates them.
            $stop = $i;
            while ($stop + 1 < $count
                && !$rest[$stop + 1]->is(WordClass::PREPOSITION)
                && !$rest[$stop + 1]->is(WordClass::ADVERB)
                && !$rest[$stop + 1]->is(WordClass::ARTICLE)
            ) {
                $stop++;
            }

            $core = ['start' => $offset + $i, 'end' => $offset + $stop];
            if ($nounSeen === 0) {
                $cores[] = ['role' => 'object'] + $core;
            } elseif ($nounSeen === 1 && $dative) {
                $cores[] = ['role' => 'object2'] + $core;
            } elseif ($nounSeen === 1 && $prepositional) {
                // The thing listened to, given without a preposition.
                $cores[] = ['role' => 'prepositional'] + $core;
            } else {
                $adjuncts[] = ['role' => 'accompaniment'] + $core;
            }
            $nounSeen++;

            $i = $stop + 1;
        }

        return ['cores' => $cores, 'adjuncts' => $adjuncts];
    }

    /**
     * @param list<array{role: string, start: int, end: int}> $cores
     */
    private function scheme(Token $verb, array $cores): string
    {
        if ($verb->feature('linking') === 'yes') {
            return Scheme::SUBJECT_PREDICATE;
        }

        $named = array_column($cores, 'role');
        if (in_array('prepositional', $named, true)) {
            return Scheme::SUBJECT_VERB_PREPOSITIONAL;
        }

        $objects = array_filter($named, static fn (string $r): bool => str_starts_with($r, 'object'));
        if (count($objects) >= 2) {
            return Scheme::SUBJECT_VERB_OBJECT_OBJECT;
        }
        if (count($objects) === 1) {
            return Scheme::SUBJECT_VERB_OBJECT;
        }

        return Scheme::SUBJECT_VERB;
    }

    /**
     * Cores read in the order a person says them, which is the order the tokens
     * already sit in.
     *
     * @param list<array{role: string, start: int, end: int}> $cores
     * @return list<array{role: string, start: int, end: int}>
     */
    private function reorder(array $cores): array
    {
        usort($cores, static fn (array $a, array $b): int => $a['start'] <=> $b['start']);

        return $cores;
    }

    private function prepositionRole(string $word): string
    {
        return match (strtolower($word)) {
            'in', 'on', 'at', 'under', 'over', 'between', 'into' => 'place',
            'after', 'before', 'during' => 'time',
            'with', 'without' => 'accompaniment',
            'for' => 'reason',
            'about', 'of' => 'reason',
            default => 'place',
        };
    }

    private function looksLikeProperName(Token $token): bool
    {
        return $token->is(WordClass::NOUN) && Tokenizer::startsUpper($token->text);
    }

    private function verbTakesPrepositional(Token $verb): bool
    {
        if (($verb->feature('valence') ?? '') === 'prepositional') {
            return true;
        }

        $askers = ['listen', 'waits', 'wait', 'looks', 'look', 'thinks', 'think', 'talks', 'talk', 'arrives', 'arrive'];

        return in_array(strtolower($verb->text), $askers, true);
    }

    private function verbTakesDative(Token $verb): bool
    {
        if (($verb->feature('valence') ?? '') === 'dative') {
            return true;
        }

        $givers = ['give', 'gives', 'gave', 'send', 'sends', 'sent', 'tell', 'tells', 'told', 'show', 'shows', 'showed', 'bring', 'brings', 'brought', 'buy', 'buys', 'bought', 'offer', 'offers', 'teach', 'teaches', 'taught'];

        return in_array(strtolower($verb->text), $givers, true);
    }
}
