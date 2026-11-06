<?php

declare(strict_types=1);

use Interface\Syntax\Lexicon;
use Interface\Syntax\WordClass;
use Interface\Tests\Assert;

return [
    ['the sixteen word classes are all reachable by name', static function (): void {
        Assert::count(16, WordClass::all());
        Assert::true(WordClass::knows('verb'));
        Assert::false(WordClass::knows('adverb of time'));
    }],
    ['an invariable class reports no slots', static function (): void {
        Assert::true(WordClass::isInvariant('preposition'));
        Assert::true(WordClass::isInvariant('conjunction'));
        Assert::false(WordClass::isInvariant('noun'));
        Assert::count(0, WordClass::slots('particle'));
    }],
    ['a verb carries six slots of morphology', static function (): void {
        $slots = WordClass::slots('verb');
        Assert::count(6, $slots);
        Assert::contains('aspect', implode(',', $slots));
    }],
    ['only the classes with person and number are personal', static function (): void {
        $personal = WordClass::personal();
        Assert::contains('pronoun', implode(',', $personal));
        Assert::contains('verb', implode(',', $personal));
        Assert::false(in_array('noun', $personal, true));
    }],
    ['the lexicon knows a word and its second class', static function (): void {
        $lex = Lexicon::english();
        Assert::true($lex->has('book'));
        Assert::same(WordClass::NOUN, $lex->primary('book'));
        Assert::same(WordClass::AUXILIARY, $lex->primary('has'));
        Assert::contains(WordClass::VERB, implode(',', $lex->classes('has')));
    }],
    ['an unknown word has no class at all', static function (): void {
        $lex = Lexicon::english();
        Assert::false($lex->has('zorblaxian'));
        Assert::count(0, $lex->classes('zorblaxian'));
    }],
    ['the lexicon marks the linking verbs', static function (): void {
        $lex = Lexicon::english();
        Assert::same('yes', $lex->features('is')['linking'] ?? '');
        Assert::same('yes', $lex->features('seems')['linking'] ?? '');
        Assert::same([], $lex->features('run'));
    }],
    ['the lexicon holds enough words to parse a lesson', static function (): void {
        Assert::true(Lexicon::english()->size() > 200, 'the table is smaller than a lesson');
    }],
];
