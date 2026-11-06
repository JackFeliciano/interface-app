<?php

declare(strict_types=1);

namespace Interface\App;

use Interface\Chat\Prompt;
use Interface\Engine;
use Interface\Lexis\Lemma;
use Interface\Syntax\Sentence;

/** Every screen, as a string. No template engine, no dependencies. */
final class Views
{
    public static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function page(string $title, string $body, string $base): string
    {
        $b = self::esc($base);

        return '<!doctype html>' . "\n"
            . '<html lang="en">' . "\n"
            . '<head>' . "\n"
            . '<meta charset="utf-8">' . "\n"
            . '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n"
            . '<title>' . self::esc($title) . ' | Interface</title>' . "\n"
            . '<link rel="stylesheet" href="' . $b . 'assets/site.css">' . "\n"
            . '</head>' . "\n"
            . '<body>' . "\n"
            . self::nav($base)
            . '<main>' . "\n" . $body . '</main>' . "\n"
            . '<footer><p>Interface, the syntax engine, the lexical database, the conversation engine and the path.</p></footer>' . "\n"
            . '</body>' . "\n"
            . '</html>' . "\n";
    }

    private static function nav(string $base): string
    {
        $b = self::esc($base);
        $links = [
            'Parse a sentence' => 'parse',
            'Lexical records' => 'lexis',
            'Practice prompt' => 'chat',
            'The path' => 'path',
        ];
        $out = '<header><a class="mark" href="' . $b . '">Interface</a><nav>';
        foreach ($links as $label => $path) {
            $out .= '<a href="' . $b . $path . '">' . self::esc($label) . '</a>';
        }
        $out .= '</nav></header>' . "\n";

        return $out;
    }

    /** @param list<array{0: string, 1: string, 2: string}> $rows */
    public static function home(string $base, array $rows, Engine $engine): string
    {
        $out = '<h1>The Interface language course</h1>' . "\n";
        $out .= '<p class="lede">An intensive Indo-European language course. One engine sorts the sentences, one database holds the words and derives their forms, one engine turns a lesson into a conversation, and the path carries a learner from English to Portuguese to Russian.</p>' . "\n";
        $out .= '<p>Interface is an intensive language course. One engine serves every Indo-European language. The course draws on Chomsky\'s generative grammar, which treats the constructions beneath languages as members of one universal set. Learners study sentence structures across languages. Frequency lists guide the selection and order of vocabulary for each level. Lessons bring these elements together through sentence building, with AI-assisted prompts for conversation practice.</p>' . "\n";

        $out .= '<h2>What the engine does with a sentence</h2>' . "\n";
        $out .= '<table><thead><tr><th>Sentence</th><th>Scheme</th><th>Cores</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $out .= '<tr><td>' . self::esc($row[0]) . '</td><td><code>' . self::esc($row[1]) . '</code></td><td>' . self::esc($row[2]) . '</td></tr>';
        }
        $out .= '</tbody></table>' . "\n";

        $out .= '<h2>The four parts</h2>' . "\n";
        $out .= '<ul class="parts">';
        $out .= '<li><strong>Syntax.</strong> Five schemes, sixteen word classes, cores separated from the adjuncts that orbit them.</li>';
        $out .= '<li><strong>Lexis.</strong> ' . $engine->lexis->size() . ' concepts, each filed under one of the eleven families, in three languages.</li>';
        $out .= '<li><strong>Chat.</strong> ' . $engine->constructions->size() . ' constructions, including the one where the thing liked is the subject and the person carries the dative.</li>';
        $out .= '<li><strong>Path.</strong> ' . $engine->path->size() . ' lessons across ' . count($engine->path->languages()) . ' languages.</li>';
        $out .= '</ul>' . "\n";

        return $out;
    }

    public static function parse(string $base, ?Sentence $sentence, string $text, string $error): string
    {
        $b = self::esc($base);
        $out = '<h1>Parse a sentence</h1>' . "\n";
        $out .= '<form method="post" action="' . $b . 'parse"><label for="sentence">A sentence in English</label>'
            . '<input id="sentence" name="sentence" value="' . self::esc($text) . '" placeholder="The teacher gives the student a book">'
            . '<button type="submit">Sort it</button></form>' . "\n";

        if ($error !== '') {
            $out .= '<p class="error">' . self::esc($error) . '</p>' . "\n";
        }

        if ($sentence !== null) {
            $out .= '<table><thead><tr><th>Word</th><th>Class</th><th>Features</th></tr></thead><tbody>';
            foreach ($sentence->tokens as $token) {
                $features = [];
                foreach ($token->features as $key => $value) {
                    $features[] = $key . '=' . $value;
                }
                $out .= '<tr><td>' . self::esc($token->text) . '</td><td>' . self::esc($token->wordClass) . '</td><td>' . self::esc(implode(', ', $features)) . '</td></tr>';
            }
            $out .= '</tbody></table>' . "\n";

            $out .= '<p class="scheme">Scheme <code>' . self::esc($sentence->scheme) . '</code></p>' . "\n";
            $out .= '<p>Cores: <strong>' . self::esc($sentence->coreText()) . '</strong></p>' . "\n";
            if ($sentence->adjuncts !== []) {
                $out .= '<p>Adjuncts orbiting them: ' . self::esc($sentence->adjunctText()) . '</p>' . "\n";
            }
        }

        return $out;
    }

    /**
     * @param list<string> $forms
     * @param list<string> $concepts
     */
    public static function lexis(string $base, ?Lemma $lemma, string $concept, string $language, array $forms, array $concepts): string
    {
        $b = self::esc($base);
        $out = '<h1>Lexical records</h1>' . "\n";
        $out .= '<p class="lede">Each record stores a concept and its lemmas, and the engine derives the forms. A verb yields every form it takes. A regular noun nobody entered still declines.</p>' . "\n";
        $out .= '<form method="post" action="' . $b . 'lexis"><label for="concept">Concept</label><input id="concept" name="concept" value="' . self::esc($concept) . '">'
            . '<label for="language">Language</label><select id="language" name="language">';
        foreach (['en', 'pt', 'ru'] as $code) {
            $out .= '<option value="' . $code . '"' . ($code === $language ? ' selected' : '') . '>' . $code . '</option>';
        }
        $out .= '</select><button type="submit">Show the record</button></form>' . "\n";

        if ($lemma === null) {
            $out .= '<p class="error">No record for ' . self::esc($concept) . '.</p>' . "\n";
        } else {
            $out .= '<h2>' . self::esc($lemma->concept) . '</h2>';
            $out .= '<dl class="record">';
            $out .= '<dt>Part of speech</dt><dd>' . self::esc($lemma->partOfSpeech) . '</dd>';
            $out .= '<dt>Semantic group</dt><dd>' . self::esc($lemma->semanticGroup) . '</dd>';
            $out .= '<dt>Morphological family</dt><dd>' . self::esc($lemma->morphologicalFamily) . '</dd>';
            $out .= '<dt>Sense definition</dt><dd>' . self::esc($lemma->definition) . '</dd>';
            foreach ($lemma->words as $code => $word) {
                $out .= '<dt>' . self::esc($code) . '</dt><dd>' . self::esc($word) . '</dd>';
            }
            $out .= '</dl>';
            $out .= '<h3>Forms derived for ' . self::esc($language) . '</h3>';
            $out .= '<p class="forms">' . self::esc(implode(' · ', $forms)) . '</p>';
            if ($lemma->senses !== []) {
                $out .= '<h3>The senses, and what English blurs</h3><ul>';
                foreach ($lemma->senses as $sense) {
                    $marks = [];
                    foreach ($sense->marks as $key => $value) {
                        $marks[] = $key . ': ' . $value;
                    }
                    $out .= '<li>' . self::esc($sense->language) . ', ' . self::esc($sense->gloss)
                        . ($marks === [] ? '' : ' <em>(' . self::esc(implode(', ', $marks)) . ')</em>') . '</li>';
                }
                $out .= '</ul>';
            }
        }

        $out .= '<p class="small">' . count($concepts) . ' concepts in the seed database: ' . self::esc(implode(', ', $concepts)) . '</p>' . "\n";

        return $out;
    }

    public static function chat(string $base, ?Prompt $prompt, int $number, Engine $engine): string
    {
        $b = self::esc($base);
        $out = '<h1>Practice prompt</h1>' . "\n";
        $out .= '<p class="lede">Pick a lesson. The engine writes a prompt that carries the vocabulary, the constructions and the special cases, and you hand it to any AI assistant.</p>' . "\n";
        $out .= '<form method="post" action="' . $b . 'chat"><label for="lesson">Lesson</label><select id="lesson" name="lesson">';
        foreach ($engine->path->lessons() as $lesson) {
            $out .= '<option value="' . $lesson->number . '"' . ($lesson->number === $number ? ' selected' : '') . '>'
                . $lesson->number . '. ' . self::esc($lesson->title) . ' (' . strtoupper($lesson->language) . ')</option>';
        }
        $out .= '</select><button type="submit">Write the prompt</button></form>' . "\n";

        if ($prompt === null) {
            $out .= '<p class="error">That lesson has no prompt.</p>' . "\n";
        } else {
            $out .= '<p class="small">A ' . $prompt->words() . ' word prompt, target language ' . self::esc($prompt->target) . '.</p>';
            $out .= '<textarea class="prompt" rows="24" readonly>' . self::esc($prompt->text) . '</textarea>';
        }

        return $out;
    }

    public static function path(string $base, Engine $engine): string
    {
        $out = '<h1>The path</h1>' . "\n";
        $out .= '<p class="lede">English carries the structures. Portuguese and Russian arrive as the same structures with different words.</p>' . "\n";
        $out .= '<ol class="path">';
        foreach ($engine->path->lessons() as $lesson) {
            $out .= '<li><span class="number">' . $lesson->number . '</span>'
                . '<span class="lang">' . strtoupper(self::esc($lesson->language)) . '</span>'
                . '<span class="title">' . self::esc($lesson->title) . '</span>'
                . '<span class="goal">' . self::esc($lesson->goal) . '</span></li>';
        }
        $out .= '</ol>' . "\n";

        return $out;
    }

    public static function notFound(string $base): string
    {
        return self::page('Not found', '<h1>Not found</h1><p>That page does not exist. <a href="' . self::esc($base) . '">Back to the course</a>.</p>', $base);
    }
}
