<?php

declare(strict_types=1);

use Interface\Lexis\Database;
use Interface\Lexis\Inflector;
use Interface\Lexis\PartOfSpeech;
use Interface\Tests\Assert;

$db = static fn (): Database => Database::seeded();

return [
    ['eleven families carry a part of speech', static function (): void {
        Assert::same(11, count(PartOfSpeech::all()));
        Assert::true(PartOfSpeech::knows('noun'));
        Assert::false(PartOfSpeech::knows('adposition'));
    }],
    ['a verb yields every form it takes', static function () use ($db): void {
        $forms = $db()->forms('read');
        foreach (['read', 'reads', 'reading'] as $form) {
            Assert::true(in_array($form, $forms, true), 'missing ' . $form);
        }
    }],
    ['a regular noun nobody entered still declines', static function (): void {
        $inflector = new Inflector();
        Assert::same('cities', $inflector->plural('city'));
        Assert::same('boxes', $inflector->plural('box'));
        Assert::same('houses', $inflector->plural('house'));
    }],
    ['a verb nobody entered still conjugates', static function (): void {
        $inflector = new Inflector();
        $forms = $inflector->forms(new Interface\Lexis\Lemma(
            'arrive',
            PartOfSpeech::VERB,
            'motion',
            'weak-verb',
            ['en' => 'arrive'],
            [],
            'to reach a place',
        ));
        Assert::true(in_array('arrives', $forms, true), 'third person');
        Assert::true(in_array('arrived', $forms, true), 'past');
        Assert::true(in_array('arriving', $forms, true), 'gerund');
    }],
    ['every record carries the four fields', static function () use ($db): void {
        foreach ($db()->concepts() as $concept) {
            $lemma = $db()->get($concept);
            Assert::true($lemma->partOfSpeech !== '', $concept . ' has no part of speech');
            Assert::true($lemma->semanticGroup !== '', $concept . ' has no semantic group');
            Assert::true($lemma->morphologicalFamily !== '', $concept . ' has no morphological family');
            Assert::true($lemma->definition !== '', $concept . ' has no definition');
        }
    }],
    ['English leads and Portuguese and Russian sit beside it', static function () use ($db): void {
        $languages = $db()->get('water')->languages();
        Assert::same('en', $languages[0]);
        Assert::true(in_array('pt', $languages, true));
        Assert::true(in_array('ru', $languages, true));
    }],
    ['the marks name what English blurs', static function () use ($db): void {
        $marks = [];
        foreach ($db()->markedSenses() as $row) {
            foreach ($row['sense']['marks'] as $key => $value) {
                $marks[$key] = $value;
            }
        }
        Assert::true(isset($marks['aspect']), 'Russian aspect is not marked');
        Assert::true(isset($marks['tense']), 'the Portuguese past is not marked');
    }],
    ['a derived form finds its concept', static function () use ($db): void {
        Assert::true(in_array('house', $db()->find('houses'), true));
        Assert::true(in_array('man', $db()->find('men'), true));
    }],
];
