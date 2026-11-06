# Interface

Interface is an intensive language course. One engine serves every Indo-European language. The course draws on Chomsky's generative grammar, which treats the constructions beneath languages as members of one universal set. Learners study sentence structures across languages. Frequency lists guide the selection and order of vocabulary for each level. Lessons bring these elements together through sentence building, with AI-assisted prompts for conversation practice.

LEX stores lemmas and derives their word forms, while frequency lists guide vocabulary selection. GRAM classifies sentences, tracks morphology across sixteen word classes and checks learner-built sentences. CHAT turns each lesson's vocabulary, constructions and special cases into a prompt for any AI assistant. APP delivers the course through a direct lesson path, and MOBILE brings the same course to a phone.

## The four parts

### Syntax

The syntax engine sorts every sentence into one of five schemes, from subject with a linking verb and a predicate subject through to subject, verb and a prepositional object, and it separates the cores from the adjuncts that orbit them. Sixteen word classes carry the morphology, taught in the learner's own language first, so the learner sees the same structures under every Indo-European language.

The five schemes:

1. subject, linking verb, predicate
2. subject, verb
3. subject, verb, object
4. subject, verb, object, object
5. subject, verb, prepositional object

The learner builds simple, compound and complex sentences and the engine checks each one.

### Lexis

A lemma-first lexical database. It stores concepts and lemmas and derives the word forms, so a verb yields every form it takes and a regular noun nobody entered still declines. English leads, and Portuguese and Russian sit beside it to mark the senses English blurs, such as Russian aspect and the two Portuguese past tenses. Each record carries a part of speech from the eleven families, a semantic group, the morphological family and one sense definition.

The inflection tables are English's, and they are regular by design. A record whose forms are irregular carries them under `extra.forms`. A record whose target language has its own forms carries them under `extra.forms_<code>`.

### Chat

A conversation practice engine. Learners build sentences and meet the constructions a language does on its own terms, such as the Italian mi piace or the Spanish me gusta, where the thing liked stands as the subject and the person carries the dative. Chat turns each lesson into a practice prompt that carries the vocabulary, the constructions and the special cases, and the learner hands that prompt to any AI assistant for a conversation.

The prompt is plain text on purpose. It survives a paste into a chat window, an attachment, or a note on a phone.

### The application

The course path follows Duolingo's shape with a much faster and more direct path. Twelve lessons open in the seed path: five in English, four in Portuguese, three in Russian. Each lesson names its schemes, its concepts and its constructions.

## Running it

Requirements: PHP 8.2 or newer. There is no Composer, no vendor directory and no dependency to install.

```
php -S 127.0.0.1:8096 -t showroom showroom/index.php
```

Then open `http://127.0.0.1:8096/`. The screens are:

- `/` the four parts, with six sentences parsed on the spot
- `/parse` a sentence, its word classes and its cores
- `/lexis` a concept record, the forms derived for a language, and the senses English blurs
- `/chat` a lesson turned into a practice prompt
- `/path` the twelve lessons
- `/health` a JSON answer

When the application sits under a path prefix, set `INTERFACE_BASE` to that prefix with a trailing slash.

## The command line

```
php bin/interface parse "The teacher gives the student a book"
php bin/interface forms read ru
php bin/interface lookup men
php bin/interface constructions
php bin/interface path
php bin/interface prompt 8
```

## Tests

```
php tests/run.php
```

The runner holds no dependency and each test file returns a list of name and closure pairs. The suite reports a count of passed, failed and total checks.

## Layout

```
autoload.php          maps Interface\ to src/
bin/interface         the command line
src/Engine.php        one object over the four parts
src/Syntax/           word classes, tokenizer, tagger, parser, the five schemes
src/Lexis/            parts of speech, senses, lemmas, the inflector, the database
src/Chat/             constructions, the prompt builder, the prompt
src/Course/           lessons and the path
src/App/              the router, the views, the front controller
showroom/             the front controller entry and the stylesheet
deploy/               the systemd unit and the nginx block
tests/                the runner and the suite
```

## Licence

Mozilla Public License 2.0. See `LICENSE`.
