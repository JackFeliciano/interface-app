<?php

declare(strict_types=1);

use Interface\Syntax\Lexicon;
use Interface\Syntax\Tagger;
use Interface\Syntax\WordClass;
use Interface\Tests\Assert;

return [
    ['a determiner forces the head of the phrase to be a noun', static function (): void {
        $tagger = new Tagger(Lexicon::english());
        $tokens = $tagger->tag('The big house');
        Assert::same(WordClass::NOUN, $tokens[2]->wordClass);
    }],
    ['a subject pronoun is followed by its verb', static function (): void {
        $tagger = new Tagger(Lexicon::english());
        $tokens = $tagger->tag('They read');
        Assert::same(WordClass::PRONOUN, $tokens[0]->wordClass);
        Assert::same(WordClass::VERB, $tokens[1]->wordClass);
    }],
    ['an unknown word becomes a noun and leaves a note', static function (): void {
        $tagger = new Tagger(Lexicon::english());
        $tokens = $tagger->tag('The zorblax sleeps');
        Assert::same(WordClass::NOUN, $tokens[1]->wordClass);
        Assert::contains('zorblax', implode(' ', $tagger->notes()));
    }],
    ['a word in -ly becomes an adverb without the lexicon', static function (): void {
        $tagger = new Tagger(Lexicon::english());
        $tokens = $tagger->tag('He talks quietly');
        Assert::same(WordClass::ADVERB, $tokens[2]->wordClass);
    }],
    ['the tagger never throws on an empty line', static function (): void {
        $tagger = new Tagger(Lexicon::english());
        Assert::count(0, $tagger->tag('   '));
    }],
];
