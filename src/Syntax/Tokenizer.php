<?php

declare(strict_types=1);

namespace Interface\Syntax;

/** Splits a line into tokens. A word is what sits between spaces and terminal marks. */
final class Tokenizer
{
    /** @return list<string> */
    public static function words(string $text): array
    {
        $clean = preg_replace('/[\p{Pd}\x{201C}\x{201D}]/u', ' ', $text) ?? $text;
        $clean = preg_replace('/[.,!?;:]+/u', ' ', $clean) ?? $clean;
        $parts = preg_split('/\s+/u', trim($clean)) ?: [];

        return array_values(array_filter($parts, static fn (string $w): bool => $w !== ''));
    }

    public static function isSentenceEnd(string $text): bool
    {
        $last = rtrim($text);
        if ($last === '') {
            return false;
        }

        return in_array($last[strlen($last) - 1], ['.', '!', '?'], true);
    }

    public static function startsUpper(string $word): bool
    {
        if ($word === '') {
            return false;
        }

        $first = mb_substr($word, 0, 1);

        return $first !== '' && mb_strtoupper($first) === $first && mb_strtolower($first) !== $first;
    }
}
