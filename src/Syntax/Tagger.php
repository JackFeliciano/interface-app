<?php

declare(strict_types=1);

namespace Interface\Syntax;

/**
 * Gives every word a class. The lexicon supplies the candidates and two passes
 * of context narrow them: after a determiner comes the head of a noun phrase,
 * and after a subject pronoun comes a verb that agrees with it.
 *
 * The tagger never throws. A word it has never seen is tagged as a noun and the
 * parse carries a note naming it, so a learner meets a guess instead of a crash.
 */
final class Tagger
{
    /** @var list<string> */
    private array $notes = [];

    public function __construct(private readonly Lexicon $lexicon)
    {
    }

    /** @return list<Token> */
    public function tag(string $text, ?Lexicon $overrides = null): array
    {
        $this->notes = [];
        $words = Tokenizer::words($text);
        $lex = $overrides ?? $this->lexicon;

        $classes = [];
        foreach ($words as $i => $word) {
            $candidates = $lex->classes($word);
            if ($candidates === []) {
                $candidates = $this->guess($word, $i);
                if ($candidates === [WordClass::NOUN]) {
                    $this->notes[] = 'unknown word: ' . $word;
                }
            }
            $classes[$i] = $candidates;
        }

        $classes = $this->narrow($words, $classes, $lex);

        $tokens = [];
        foreach ($words as $i => $word) {
            $class = $classes[$i][0];
            $features = $lex->features($word);
            $tokens[] = new Token($word, $i, $class, $features);
        }

        return $tokens;
    }

    /** @return list<string> */
    public function notes(): array
    {
        return $this->notes;
    }

    /**
     * The first pass. A determiner forces the next non-adjective word to be a
     * noun. A subject pronoun forces the next word to be a verb when it can be.
     *
     * @param list<string> $words
     * @param array<int, list<string>> $classes
     * @return array<int, list<string>>
     */
    private function narrow(array $words, array $classes, Lexicon $lex): array
    {
        $count = count($words);

        for ($i = 0; $i < $count; $i++) {
            $head = $classes[$i][0] ?? WordClass::NOUN;

            if ($head === WordClass::ARTICLE) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if (in_array(WordClass::ADJECTIVE, $classes[$j], true) && $classes[$j][0] === WordClass::ADJECTIVE) {
                        continue;
                    }
                    if (in_array(WordClass::NOUN, $classes[$j], true)) {
                        $classes[$j] = [WordClass::NOUN];
                    }
                    break;
                }
                continue;
            }

            // A subject pronoun in first position is followed by its verb, and a
            // dative or prepositional verb is followed by the phrase it governs.
            if ($i === 0 && $head === WordClass::PRONOUN && ($lex->features($words[$i])['person'] ?? '') === 'subject') {
                for ($j = $i + 1; $j < $count; $j++) {
                    if (($classes[$j][0] ?? '') === WordClass::ADVERB) {
                        continue;
                    }
                    foreach ($classes[$j] as $candidate) {
                        if ($candidate === WordClass::VERB || $candidate === WordClass::AUXILIARY) {
                            $classes[$j] = [$candidate];
                            break 2;
                        }
                    }
                    break;
                }
                continue;
            }

            if ($head === WordClass::VERB && ($lex->features($words[$i])['valence'] ?? '') === 'dative') {
                for ($j = $i + 1, $phrases = 0; $j < $count && $phrases < 2; $j++) {
                    if (($classes[$j][0] ?? '') === WordClass::PREPOSITION || ($classes[$j][0] ?? '') === WordClass::ADVERB) {
                        continue;
                    }
                    foreach ($classes[$j] as $candidate) {
                        if ($candidate === WordClass::NOUN || $candidate === WordClass::PRONOUN) {
                            $classes[$j] = [WordClass::NOUN];
                            $phrases++;
                            break;
                        }
                    }
                }
            }
        }

        return $classes;
    }

    /**
     * A word the lexicon has never seen. A capital in mid-sentence is a proper
     * noun, a final -ly is an adverb, a final -ing is a participle, and the rest
     * is a noun.
     *
     * @return list<string>
     */
    private function guess(string $word, int $index): array
    {
        $lower = strtolower($word);
        if (str_ends_with($lower, 'ly') && strlen($lower) > 3) {
            return [WordClass::ADVERB];
        }
        if (str_ends_with($lower, 'ing') && strlen($lower) > 5) {
            return [WordClass::PARTICIPLE, WordClass::NOUN];
        }
        if ($index > 0 && Tokenizer::startsUpper($word)) {
            return [WordClass::NOUN];
        }

        return [WordClass::NOUN];
    }
}
