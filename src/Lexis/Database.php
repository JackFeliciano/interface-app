<?php

declare(strict_types=1);

namespace Interface\Lexis;

/**
 * The lexical database. It holds concepts, and it derives the forms on demand,
 * so the store stays small and a noun nobody entered still declines.
 */
final class Database
{
    /** @var array<string, Lemma> concept => record */
    private array $concepts = [];

    private Inflector $inflector;

    public function __construct(?Inflector $inflector = null)
    {
        $this->inflector = $inflector ?? new Inflector();
    }

    public function add(Lemma $lemma): void
    {
        $this->concepts[strtolower($lemma->concept)] = $lemma;
    }

    public function get(string $concept): ?Lemma
    {
        return $this->concepts[strtolower($concept)] ?? null;
    }

    public function has(string $concept): bool
    {
        return isset($this->concepts[strtolower($concept)]);
    }

    /** @return list<string> */
    public function concepts(): array
    {
        return array_keys($this->concepts);
    }

    public function size(): int
    {
        return count($this->concepts);
    }

    /** @return list<Lemma> */
    public function bySemanticGroup(string $group): array
    {
        return array_values(array_filter(
            $this->concepts,
            static fn (Lemma $l): bool => strtolower($l->semanticGroup) === strtolower($group),
        ));
    }

    /** @return list<Lemma> */
    public function byFamily(string $family): array
    {
        return array_values(array_filter(
            $this->concepts,
            static fn (Lemma $l): bool => $l->partOfSpeech === $family,
        ));
    }

    /** Every form of one concept in one language. @return list<string> */
    public function forms(string $concept, string $language = 'en'): array
    {
        $lemma = $this->get($concept);

        return $lemma === null ? [] : $this->inflector->forms($lemma, $language);
    }

    /** The concept a surface form belongs to, by walking the derived forms. @return list<string> */
    public function find(string $form): array
    {
        $hits = [];
        foreach ($this->concepts as $concept => $lemma) {
            foreach ($lemma->languages() as $language) {
                if (in_array(strtolower($form), array_map('strtolower', $this->inflector->forms($lemma, $language)), true)) {
                    $hits[] = $concept;
                    break;
                }
            }
        }

        return $hits;
    }

    /** The senses English blurs, as the records mark them. @return list<array<string, mixed>> */
    public function markedSenses(): array
    {
        $out = [];
        foreach ($this->concepts as $concept => $lemma) {
            foreach ($lemma->senses as $sense) {
                if ($sense->marks !== []) {
                    $out[] = ['concept' => $concept, 'sense' => $sense->toArray()];
                }
            }
        }

        return $out;
    }

    /** The seed database the course opens with. */
    public static function seeded(): self
    {
        $db = new self();
        foreach (self::seedRecords() as $record) {
            $db->add($record);
        }

        return $db;
    }

    /** @return list<Lemma> */
    private static function seedRecords(): array
    {
        return [
            new Lemma('water', PartOfSpeech::NOUN, 'substance', 'o-stem', ['en' => 'water', 'pt' => 'água', 'ru' => 'вода'], [
                new Sense('en', 'the liquid that falls as rain'),
                new Sense('pt', 'o líquido que cai como chuva'),
                new Sense('ru', 'жидкость, которая падает дождём', ['aspect']),
            ], 'the liquid a person drinks and that falls as rain'),
            new Lemma('house', PartOfSpeech::NOUN, 'dwelling', 'o-stem', ['en' => 'house', 'pt' => 'casa', 'ru' => 'дом'], [
                new Sense('en', 'a building where a family lives'),
                new Sense('pt', 'o prédio onde uma família mora'),
                new Sense('ru', 'здание, в котором живёт семья'),
            ], 'a building where a person lives'),
            new Lemma('book', PartOfSpeech::NOUN, 'object', 'o-stem', ['en' => 'book', 'pt' => 'livro', 'ru' => 'книга'], [
                new Sense('en', 'a set of printed pages bound together'),
                new Sense('pt', 'um conjunto de páginas impressas'),
            ], 'a set of pages bound together that a person reads'),
            new Lemma('write', PartOfSpeech::VERB, 'action', 'strong-verb', ['en' => 'write', 'pt' => 'escrever', 'ru' => 'писать'], [
                new Sense('en', 'to put words on paper'),
                new Sense('ru', 'писать', ['aspect' => 'imperfective']),
                new Sense('ru', 'написать', ['aspect' => 'perfective']),
            ], 'to put words on a surface', ['forms' => ['write', 'writes', 'wrote', 'written', 'writing']]),
            new Lemma('read', PartOfSpeech::VERB, 'action', 'strong-verb', ['en' => 'read', 'pt' => 'ler', 'ru' => 'читать'], [
                new Sense('en', 'to take the meaning of written words'),
                new Sense('pt', 'ler no passado', ['tense' => 'imperfeito']),
                new Sense('pt', 'ter lido', ['tense' => 'perfeito']),
            ], 'to take the meaning of written words', [
                'forms' => ['read', 'reads', 'read', 'reading'],
                'forms_ru' => ['читать', 'читаю', 'читает', 'читал', 'прочитал'],
                'forms_pt' => ['ler', 'leio', 'lê', 'lia', 'li'],
            ]),
            new Lemma('go', PartOfSpeech::VERB, 'motion', 'strong-verb', ['en' => 'go', 'pt' => 'ir', 'ru' => 'идти'], [
                new Sense('en', 'to move to another place'),
                new Sense('ru', 'идти', ['aspect' => 'imperfective', 'motion' => 'on foot']),
                new Sense('ru', 'ехать', ['aspect' => 'imperfective', 'motion' => 'by vehicle']),
            ], 'to move from one place to another', ['forms' => ['go', 'goes', 'went', 'gone', 'going']]),
            new Lemma('eat', PartOfSpeech::VERB, 'consumption', 'strong-verb', ['en' => 'eat', 'pt' => 'comer', 'ru' => 'есть'], [
                new Sense('en', 'to take food into the body'),
                new Sense('pt', 'comer no passado', ['tense' => 'imperfeito']),
                new Sense('pt', 'ter comido', ['tense' => 'perfeito']),
            ], 'to take food into the body', ['forms' => ['eat', 'eats', 'ate', 'eaten', 'eating']]),
            new Lemma('big', PartOfSpeech::ADJECTIVE, 'size', 'a-stem', ['en' => 'big', 'pt' => 'grande', 'ru' => 'большой'], [
                new Sense('en', 'takes more room than usual'),
                new Sense('ru', 'большой', ['degree' => 'positive']),
            ], 'takes more room than the usual one'),
            new Lemma('small', PartOfSpeech::ADJECTIVE, 'size', 'a-stem', ['en' => 'small', 'pt' => 'pequeno', 'ru' => 'маленький'], [
                new Sense('en', 'takes less room than usual'),
            ], 'takes less room than the usual one'),
            new Lemma('dog', PartOfSpeech::NOUN, 'animal', 'o-stem', ['en' => 'dog', 'pt' => 'cachorro', 'ru' => 'собака'], [
                new Sense('en', 'a four-legged animal kept by a person'),
                new Sense('ru', 'собака', ['gender' => 'feminine']),
            ], 'a four-legged animal a person keeps'),
            new Lemma('man', PartOfSpeech::NOUN, 'person', 'n-stem', ['en' => 'man', 'pt' => 'homem', 'ru' => 'мужчина'], [
                new Sense('en', 'an adult male person'),
            ], 'an adult male person', ['forms' => ['man', 'men']]),
            new Lemma('teacher', PartOfSpeech::NOUN, 'person', 'o-stem', ['en' => 'teacher', 'pt' => 'professor', 'ru' => 'учитель'], [
                new Sense('en', 'a person who teaches'),
            ], 'a person whose work is to teach'),
            new Lemma('student', PartOfSpeech::NOUN, 'person', 'n-stem', ['en' => 'student', 'pt' => 'estudante', 'ru' => 'студент'], [
                new Sense('en', 'a person who studies'),
            ], 'a person who studies'),
            new Lemma('language', PartOfSpeech::NOUN, 'abstract', 'o-stem', ['en' => 'language', 'pt' => 'língua', 'ru' => 'язык'], [
                new Sense('en', 'the system of words a people speaks'),
                new Sense('ru', 'язык', ['polysemy' => 'tongue, language']),
            ], 'the system of words a people speaks'),
        ];
    }
}
