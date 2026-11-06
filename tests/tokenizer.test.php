<?php

declare(strict_types=1);

use Interface\Syntax\Tokenizer;
use Interface\Tests\Assert;

return [
    ['punctuation does not become a word', static function (): void {
        Assert::same(['The', 'dog', 'runs'], Tokenizer::words('The dog runs.'));
        Assert::same(['Is', 'it', 'ready'], Tokenizer::words('Is it ready?'));
    }],
    ['an apostrophe word stays one word', static function (): void {
        Assert::same(["don't", 'go'], Tokenizer::words("don't go"));
    }],
    ['a sentence end is one of three marks', static function (): void {
        Assert::true(Tokenizer::isSentenceEnd('ready.'));
        Assert::true(Tokenizer::isSentenceEnd('ready!'));
        Assert::true(Tokenizer::isSentenceEnd('ready?'));
        Assert::false(Tokenizer::isSentenceEnd('ready'));
    }],
    ['a capital is measured on the letter', static function (): void {
        Assert::true(Tokenizer::startsUpper('Anna'));
        Assert::false(Tokenizer::startsUpper('anna'));
        Assert::false(Tokenizer::startsUpper(''));
    }],
];
