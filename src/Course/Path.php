<?php

declare(strict_types=1);

namespace Interface\Course;

/**
 * The path a learner walks. It follows the shape of a course like Duolingo, and
 * it takes a much faster road: one language carries the structures, and the
 * learner meets the next Indo-European language as the same structures with
 * different words.
 *
 * English leads, because the learner already holds it.
 */
final class Path
{
    /** @var list<Lesson> */
    private array $lessons = [];

    /** @param list<Lesson> $lessons */
    public function __construct(array $lessons)
    {
        usort($lessons, static fn (Lesson $a, Lesson $b): int => $a->number <=> $b->number);
        $this->lessons = $lessons;
    }

    /** @return list<Lesson> */
    public function lessons(): array
    {
        return $this->lessons;
    }

    /** @return list<Lesson> */
    public function forLanguage(string $language): array
    {
        return array_values(array_filter(
            $this->lessons,
            static fn (Lesson $l): bool => strtolower($l->language) === strtolower($language),
        ));
    }

    public function lesson(int $number): ?Lesson
    {
        foreach ($this->lessons as $lesson) {
            if ($lesson->number === $number) {
                return $lesson;
            }
        }

        return null;
    }

    public function next(int $after): ?Lesson
    {
        foreach ($this->lessons as $lesson) {
            if ($lesson->number > $after) {
                return $lesson;
            }
        }

        return null;
    }

    /** @return list<string> the languages the path teaches, in the order it reaches them */
    public function languages(): array
    {
        $out = [];
        foreach ($this->lessons as $lesson) {
            if (!in_array($lesson->language, $out, true)) {
                $out[] = $lesson->language;
            }
        }

        return $out;
    }

    public function size(): int
    {
        return count($this->lessons);
    }

    public static function seeded(): self
    {
        return new self([
            new Lesson(1, 'The house is big', 'en', ['subject-predicate'], ['house', 'big'], [], 'A person shows a friend a house.', 'Say what a thing is, using a linking verb.'),
            new Lesson(2, 'The dog sleeps', 'en', ['subject-verb'], ['dog'], [], 'A quiet morning at home.', 'Say what a thing does, with no object.'),
            new Lesson(3, 'The man reads the book', 'en', ['subject-verb-object'], ['man', 'read', 'book'], [], 'A man alone with a book.', 'Put one object after the verb.'),
            new Lesson(4, 'The teacher gives the student a book', 'en', ['subject-verb-object-object'], ['teacher', 'student', 'book'], [], 'A classroom.', 'Give something to someone, with two objects.'),
            new Lesson(5, 'The student listens to the teacher', 'en', ['subject-verb-prepositional'], ['student', 'teacher'], [], 'A lesson in progress.', 'Keep the prepositional object as a core.'),
            new Lesson(6, 'The man reads the book in the garden', 'en', ['subject-verb-object'], ['man', 'read', 'book'], [], 'A garden in the afternoon.', 'Add an adjunct and see what the sentence keeps.'),
            new Lesson(7, 'O homem le o livro', 'pt', ['subject-verb-object'], ['man', 'read', 'book'], [], 'The same scene, now in Portuguese.', 'Read the same structure under another language.'),
            new Lesson(8, 'Eu gosto do livro', 'pt', ['subject-verb-object'], ['book'], ['the liked thing is the subject'], 'Recommending a book.', 'Meet the construction where the book is the subject.'),
            new Lesson(9, 'Eu lia quando ele chegou', 'pt', ['subject-verb'], ['book', 'read'], ['the past splits in two'], 'A story interrupted.', 'Choose between the imperfect and the preterite.'),
            new Lesson(10, 'Дом большой', 'ru', ['subject-predicate'], ['house', 'big'], [], 'The same scene in Russian, with no linking verb.', 'See a language that leaves the copula out.'),
            new Lesson(11, 'Я читал книгу', 'ru', ['subject-verb-object'], ['read', 'book'], ['aspect-pair'], 'An unfinished reading.', 'Pick the imperfective and say what was under way.'),
            new Lesson(12, 'Я прочитал книгу', 'ru', ['subject-verb-object'], ['read', 'book'], ['aspect-pair'], 'A finished reading.', 'Pick the perfective and say what got done.'),
        ]);
    }
}
