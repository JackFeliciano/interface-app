<?php

declare(strict_types=1);

namespace Interface\Course;

/** One step of the path: a target language, a vocabulary list and the structures it opens. */
final class Lesson
{
    /**
     * @param list<string> $grammarSchemes the schemes this lesson drills
     * @param list<string> $concepts the lexical records the lesson uses
     * @param list<string> $constructions the CHAT constructions the lesson teaches
     */
    public function __construct(
        public readonly int $number,
        public readonly string $title,
        public readonly string $language,
        public readonly array $grammarSchemes,
        public readonly array $concepts,
        public readonly array $constructions,
        public readonly string $scene,
        public readonly string $goal,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'title' => $this->title,
            'language' => $this->language,
            'grammarSchemes' => $this->grammarSchemes,
            'concepts' => $this->concepts,
            'constructions' => $this->constructions,
            'scene' => $this->scene,
            'goal' => $this->goal,
        ];
    }
}
