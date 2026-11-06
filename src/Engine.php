<?php

declare(strict_types=1);

namespace Interface;

use Interface\Chat\ConstructionBook;
use Interface\Chat\Prompt;
use Interface\Chat\PromptBuilder;
use Interface\Course\Path;
use Interface\Lexis\Database;
use Interface\Syntax\Lexicon;
use Interface\Syntax\Parser;
use Interface\Syntax\Sentence;

/**
 * The four parts of the course behind one object. GRAM sorts a sentence, LEX
 * holds the records and derives their forms, CHAT builds a practice prompt, and
 * the path says what comes next.
 */
final class Engine
{
    private static ?self $shared = null;

    public readonly Parser $parser;
    public readonly Database $lexis;
    public readonly ConstructionBook $constructions;
    public readonly Path $path;

    public function __construct(
        ?Parser $parser = null,
        ?Database $lexis = null,
        ?ConstructionBook $constructions = null,
        ?Path $path = null,
    ) {
        $this->parser = $parser ?? new Parser(Lexicon::english());
        $this->lexis = $lexis ?? Database::seeded();
        $this->constructions = $constructions ?? ConstructionBook::seeded();
        $this->path = $path ?? Path::seeded();
    }

    public static function shared(): self
    {
        return self::$shared ??= new self();
    }

    public function parse(string $sentence): Sentence
    {
        return $this->parser->parse($sentence);
    }

    /** @return list<string> */
    public function forms(string $concept, string $language = 'en'): array
    {
        return $this->lexis->forms($concept, $language);
    }

    public function promptForLesson(int $number): ?Prompt
    {
        $lesson = $this->path->lesson($number);
        if ($lesson === null) {
            return null;
        }

        $vocabulary = [];
        foreach ($lesson->concepts as $concept) {
            $lemma = $this->lexis->get($concept);
            $word = $lemma?->lemma($lesson->language);
            if ($word !== null) {
                $forms = $this->lexis->forms($concept, $lesson->language);
                $vocabulary[] = $word . ' (' . implode(', ', array_slice($forms, 1, 3)) . ')';
            }
        }

        $constructions = [];
        foreach ($lesson->constructions as $name) {
            foreach ($this->constructions->all() as $construction) {
                if ($construction->name === $name) {
                    $constructions[] = $construction->name . ': ' . $construction->pattern;
                }
            }
        }

        $specialCases = [];
        foreach ($lesson->grammarSchemes as $scheme) {
            $specialCases[] = 'aim for the scheme ' . $scheme;
        }

        return (new PromptBuilder())->build(
            $lesson->language,
            'English',
            $vocabulary,
            $constructions,
            $specialCases,
            $lesson->scene,
        );
    }
}
