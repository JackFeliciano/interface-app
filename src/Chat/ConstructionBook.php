<?php

declare(strict_types=1);

namespace Interface\Chat;

/** Every construction the course teaches, and the lookup a lesson uses. */
final class ConstructionBook
{
    /** @var list<Construction> */
    private array $all = [];

    /** @param list<Construction> $all */
    public function __construct(array $all)
    {
        $this->all = $all;
    }

    /** @return list<Construction> */
    public function all(): array
    {
        return $this->all;
    }

    /** @return list<Construction> */
    public function forLanguage(string $language): array
    {
        return array_values(array_filter(
            $this->all,
            static fn (Construction $c): bool => $c->speaks($language),
        ));
    }

    /** @return list<Construction> */
    public function byFamily(string $family): array
    {
        return array_values(array_filter(
            $this->all,
            static fn (Construction $c): bool => $c->family === $family,
        ));
    }

    public function size(): int
    {
        return count($this->all);
    }

    public static function seeded(): self
    {
        return new self([
            new Construction(
                'the liked thing is the subject',
                'dative-experiencer',
                ['it', 'es', 'pt'],
                'the thing liked stands as the subject and the person carries the dative',
                ['it: mi piace il libro', 'es: me gusta el libro', 'pt: eu gosto do livro'],
                'a word-for-word translation writes I like the book and makes the person the subject',
                'The book is what pleases. The person is in the dative, so the verb agrees with the book, not with the person, and the plural mi piacciono i libri follows the thing liked.',
            ),
            new Construction(
                'hunger is something a person has',
                'have-predicate',
                ['it', 'es', 'pt', 'fr'],
                'the state is a noun the person possesses',
                ['it: ho fame', 'es: tengo hambre', 'pt: tenho fome', 'fr: j ai faim'],
                'English says I am hungry, which puts the person inside the state',
                'A learner who copies English writes sono affamato, which means starved. The construction keeps the state as an object the person holds.',
            ),
            new Construction(
                'the verb names the direction of the motion',
                'motion-on-foot-or-vehicle',
                ['ru'],
                'one verb for going on foot and another for going by vehicle',
                ['ru: идти в школу', 'ru: ехать в школу'],
                'English says go and leaves the manner to context',
                'Russian chooses the verb from how the person travels. A learner who picks one verb for both says something the listener has to correct.',
            ),
            new Construction(
                'the reflexive carries the person back to the subject',
                'reflexive',
                ['pt', 'es', 'it', 'ru'],
                'the clitic and the verb agree with the subject together',
                ['pt: eu me levanto as sete', 'es: me levanto a las siete', 'it: mi alzo alle sette'],
                'English uses a bare verb and no clitic',
                'The clitic is not optional. Drop it and the sentence either loses its subject reference or turns the verb transitive.',
            ),
            new Construction(
                'the negative agrees with everything it negates',
                'negative-concord',
                ['ru', 'pt'],
                'a negative pronoun joins the negative particle instead of cancelling it',
                ['ru: я ничего не знаю', 'pt: eu nao sei nada'],
                'English turns the second negative positive',
                'Two negatives in Russian and Portuguese reinforce each other. Copying English and writing one negative leaves the sentence half built.',
            ),
            new Construction(
                'the past splits in two',
                'past-aspect',
                ['pt'],
                'the imperfect holds an ongoing past and the preterite closes a finished one',
                ['pt: eu lia quando ele chegou', 'pt: eu li o livro ontem'],
                'English uses one past tense for both',
                'Lia describes what was going on. Li reports what happened. The choice tells the listener whether the action had an edge.',
            ),
            new Construction(
                'the aspect pairs on every verb',
                'aspect-pair',
                ['ru'],
                'an imperfective verb names the process and a perfective verb names the result',
                ['ru: я читал книгу', 'ru: я прочитал книгу'],
                'English changes the tense and calls it a difference of time',
                'The pair is lexical, and a learner has to learn both members. The imperfective says what was under way, the perfective says what got done.',
            ),
        ]);
    }
}
