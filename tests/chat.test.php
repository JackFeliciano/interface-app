<?php

declare(strict_types=1);

use Interface\Chat\ConstructionBook;
use Interface\Chat\PromptBuilder;
use Interface\Engine;
use Interface\Tests\Assert;

$book = static fn (): ConstructionBook => ConstructionBook::seeded();

return [
    ['the book knows the dative construction', static function () use ($book): void {
        $hit = null;
        foreach ($book()->all() as $construction) {
            if ($construction->family === 'dative-experiencer') {
                $hit = $construction;
            }
        }
        Assert::true($hit !== null, 'no dative construction');
        Assert::true($hit->speaks('it'));
        Assert::true($hit->speaks('es'));
        $examples = implode(' | ', $hit->examples);
        Assert::true(str_contains($examples, 'mi piace'));
        Assert::true(str_contains($examples, 'me gusta'));
    }],
    ['the pattern says the thing liked is the subject', static function () use ($book): void {
        foreach ($book()->all() as $construction) {
            if ($construction->family === 'dative-experiencer') {
                Assert::true(str_contains($construction->pattern, 'subject'));
                Assert::true(str_contains($construction->explanation, 'dative'));
            }
        }
    }],
    ['a lesson becomes a prompt that carries the vocabulary', static function (): void {
        $prompt = (new PromptBuilder())->build(
            'it',
            'English',
            ['libro (libri)', 'casa (case)'],
            ['mi piace: the thing liked stands as the subject'],
            ['the person carries the dative'],
            'Recommending a book.',
        );
        Assert::true(str_contains($prompt->text, 'libro'));
        Assert::true(str_contains($prompt->text, 'Speak with me in it.'));
        Assert::true($prompt->characters() > 200);
    }],
    ['the engine turns a lesson number into a prompt', static function (): void {
        $engine = Engine::shared();
        $prompt = $engine->promptForLesson(8);
        Assert::true($prompt !== null, 'lesson 8 has no prompt');
        Assert::true(str_contains($prompt->text, 'pt'));
        Assert::true($engine->promptForLesson(999) === null);
    }],
    ['the path walks from English to Portuguese to Russian', static function (): void {
        $engine = Engine::shared();
        Assert::same(12, $engine->path->size());
        Assert::same(['en', 'pt', 'ru'], $engine->path->languages());
        Assert::same(2, $engine->path->next(1)->number);
        Assert::true($engine->path->lesson(13) === null);
    }],
    ['the engine answers the three layers together', static function (): void {
        $engine = Engine::shared();
        $sentence = $engine->parse('The teacher gives the student a book');
        Assert::same('subject-verb-object-object', $sentence->scheme);
        Assert::true(in_array('writes', $engine->forms('write'), true));
        Assert::true($engine->constructions->size() >= 6);
    }],
];
