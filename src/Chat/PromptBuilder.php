<?php

declare(strict_types=1);

namespace Interface\Chat;

/**
 * Turns a lesson into a practice prompt that carries the vocabulary, the
 * constructions and the special cases. The learner hands the prompt to any AI
 * assistant and talks.
 *
 * The prompt is a plain block of text on purpose. It has to survive a paste into
 * a chat window, an attachment, or a note on a phone.
 */
final class PromptBuilder
{
    /**
     * @param list<string> $vocabulary
     * @param list<string> $constructions
     * @param list<string> $specialCases
     */
    public function build(
        string $target,
        string $learnerLanguage,
        array $vocabulary,
        array $constructions,
        array $specialCases,
        string $scene = '',
    ): Prompt {
        $lines = [];
        $lines[] = 'Speak with me in ' . $target . '.';
        $lines[] = '';
        $lines[] = 'You are my conversation partner for one lesson of the Interface course. Stay inside the lesson, and correct me as we go.';
        $lines[] = '';
        $lines[] = 'My language is ' . $learnerLanguage . ', so explain anything I miss in ' . $learnerLanguage . ' first.';
        $lines[] = '';

        if ($vocabulary !== []) {
            $lines[] = 'Vocabulary I know for this lesson:';
            foreach ($vocabulary as $word) {
                $lines[] = '- ' . $word;
            }
            $lines[] = 'Use only these words unless I ask for a new one. When I need a word outside the list, say it, use it once, and write it down for me at the end.';
            $lines[] = '';
        }

        if ($constructions !== []) {
            $lines[] = 'Constructions this lesson teaches. Do not let me translate them word for word:';
            foreach ($constructions as $construction) {
                $lines[] = '- ' . $construction;
            }
            $lines[] = '';
        }

        if ($specialCases !== []) {
            $lines[] = 'Special cases to hold me to:';
            foreach ($specialCases as $case) {
                $lines[] = '- ' . $case;
            }
            $lines[] = '';
        }

        if ($scene !== '') {
            $lines[] = 'The scene: ' . $scene;
            $lines[] = '';
        }

        $lines[] = 'Rules for the conversation:';
        $lines[] = '1. One sentence at a time, and wait for mine.';
        $lines[] = '2. When I make a mistake, show the sentence I should have said, in one line, then carry on.';
        $lines[] = '3. Keep to the constructions above. If I avoid one, ask me a question that makes me use it.';
        $lines[] = '4. At the end, list the three things I got wrong most often, and the words I had to ask for.';

        return new Prompt(implode("\n", $lines), $target, $learnerLanguage);
    }
}
