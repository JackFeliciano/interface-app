<?php

declare(strict_types=1);

use Interface\Syntax\Lexicon;
use Interface\Syntax\Parser;
use Interface\Syntax\ParseError;
use Interface\Syntax\Scheme;
use Interface\Tests\Assert;

$parser = static fn (): Parser => new Parser(Lexicon::english());

return [
    ['a linking verb makes the fifth word a predicate, not an object', static function () use ($parser): void {
        $s = $parser()->parse('The house is big');
        Assert::same(Scheme::SUBJECT_PREDICATE, $s->scheme);
        Assert::same('subject', $s->cores[0]['role']);
        Assert::same('predicate', $s->cores[2]['role']);
        Assert::same('The house is big', $s->coreText());
    }],
    ['an intransitive verb leaves the subject alone', static function () use ($parser): void {
        $s = $parser()->parse('The dog sleeps');
        Assert::same(Scheme::SUBJECT_VERB, $s->scheme);
        Assert::same('The dog sleeps', $s->coreText());
        Assert::count(2, $s->cores);
        Assert::count(0, $s->adjuncts);
    }],
    ['a preposition after an intransitive verb is an adjunct', static function () use ($parser): void {
        $s = $parser()->parse('The dog sleeps in the garden');
        Assert::same(Scheme::SUBJECT_VERB, $s->scheme);
        Assert::same('in the garden', $s->adjunctText());
        Assert::same('place', $s->adjuncts[0]['role']);
    }],
    ['a transitive verb takes one object', static function () use ($parser): void {
        $s = $parser()->parse('The man reads the book');
        Assert::same(Scheme::SUBJECT_VERB_OBJECT, $s->scheme);
        Assert::same('object', $s->cores[2]['role']);
        Assert::contains('book', $s->coreText());
    }],
    ['a verb that gives takes two objects', static function () use ($parser): void {
        $s = $parser()->parse('The teacher gives the student a book');
        Assert::same(Scheme::SUBJECT_VERB_OBJECT_OBJECT, $s->scheme);
        Assert::contains('student', $s->coreText());
    }],
    ['a verb that listens keeps its prepositional object as a core', static function () use ($parser): void {
        $s = $parser()->parse('The student listens to the teacher');
        Assert::same(Scheme::SUBJECT_VERB_PREPOSITIONAL, $s->scheme);
        Assert::count(0, $s->adjuncts);
    }],
    ['the formula is readable for every scheme', static function (): void {
        foreach (Scheme::all() as $scheme) {
            Assert::true(Scheme::formula($scheme) !== '', 'no formula for ' . $scheme);
            Assert::true(Scheme::slots($scheme) !== [], 'no slots for ' . $scheme);
        }
        Assert::count(5, Scheme::all());
    }],
    ['a sentence with no verb is refused with the word that stopped it', static function () use ($parser): void {
        try {
            $parser()->parse('the big house');
            Assert::true(false, 'a verbless line parsed');
        } catch (ParseError $e) {
            Assert::contains('no verb', $e->getMessage());
            Assert::same('the', $e->word);
        }
    }],
    ['an empty line is refused', static function () use ($parser): void {
        try {
            $parser()->parse('   ');
            Assert::true(false, 'an empty line parsed');
        } catch (ParseError $e) {
            Assert::contains('nothing to parse', $e->getMessage());
        }
    }],
    ['the skeleton drops every adjunct', static function () use ($parser): void {
        $s = $parser()->parse('The man reads the book in the garden');
        Assert::same('The man reads the book', $s->skeleton());
        Assert::contains('garden', $s->adjunctText());
    }],
];
