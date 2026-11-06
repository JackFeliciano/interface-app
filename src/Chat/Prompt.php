<?php

declare(strict_types=1);

namespace Interface\Chat;

/** The block of text a learner hands to an assistant, with the two facts it names. */
final class Prompt
{
    public function __construct(
        public readonly string $text,
        public readonly string $target,
        public readonly string $learnerLanguage,
    ) {
    }

    public function characters(): int
    {
        return mb_strlen($this->text);
    }

    public function words(): int
    {
        return count(preg_split('/\s+/u', trim($this->text)) ?: []);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'target' => $this->target,
            'learnerLanguage' => $this->learnerLanguage,
            'characters' => $this->characters(),
            'words' => $this->words(),
            'text' => $this->text,
        ];
    }
}
