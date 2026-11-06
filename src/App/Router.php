<?php

declare(strict_types=1);

namespace Interface\App;

use Interface\Chat\Prompt;
use Interface\Engine;
use Interface\Syntax\ParseError;

/**
 * The course application behind one front controller. Every screen is built from
 * the engine's own answer, so what a learner sees on the page is what the engine
 * just computed.
 */
final class Router
{
    private Engine $engine;

    private string $base;

    public function __construct(?Engine $engine = null, ?string $base = null)
    {
        $this->engine = $engine ?? Engine::shared();
        $this->base = $base ?? (getenv('INTERFACE_BASE') ?: '/');
    }

    public function base(): string
    {
        return $this->base;
    }

    /** @return array{status: int, type: string, body: string} */
    public function handle(string $method, string $path, array $post): array
    {
        $path = rtrim($path, '/') ?: '/';

        return match (true) {
            $path === '/' || $path === '/index' => $this->home(),
            $path === '/parse' => $this->parse($method, $post),
            $path === '/lexis' => $this->lexis($post),
            $path === '/chat' => $this->chat($post),
            $path === '/path' => $this->path(),
            $path === '/health' => $this->json(['ok' => true, 'lessons' => $this->engine->path->size()]),
            str_starts_with($path, '/assets/') => $this->asset(substr($path, 8)),
            default => $this->notFound(),
        };
    }

    /** @return array{status: int, type: string, body: string} */
    private function home(): array
    {
        $examples = [
            'The house is big',
            'The dog sleeps',
            'The man reads the book',
            'The teacher gives the student a book',
            'The student listens to the teacher',
            'The man reads the book in the garden',
        ];

        $rows = [];
        foreach ($examples as $example) {
            try {
                $sentence = $this->engine->parse($example);
                $rows[] = [$example, $sentence->scheme, $sentence->coreText()];
            } catch (ParseError $e) {
                $rows[] = [$example, 'unparsed: ' . $e->getMessage(), ''];
            }
        }

        return $this->html('Interface', Views::home($this->base, $rows, $this->engine));
    }

    /** @return array{status: int, type: string, body: string} */
    private function parse(string $method, array $post): array
    {
        $text = trim((string) ($post['sentence'] ?? ''));
        if ($method !== 'POST' || $text === '') {
            return $this->html('Parse', Views::parse($this->base, null, '', ''));
        }

        try {
            $sentence = $this->engine->parse($text);
        } catch (ParseError $e) {
            return $this->html('Parse', Views::parse($this->base, null, $text, $e->getMessage()), 422);
        }

        return $this->html('Parse', Views::parse($this->base, $sentence, $text, ''));
    }

    /** @return array{status: int, type: string, body: string} */
    private function lexis(array $post): array
    {
        $concept = trim((string) ($post['concept'] ?? 'water'));
        $language = trim((string) ($post['language'] ?? 'en'));
        $lemma = $this->engine->lexis->get($concept);

        return $this->html('Lexis', Views::lexis(
            $this->base,
            $lemma,
            $concept,
            $language,
            $lemma === null ? [] : $this->engine->forms($concept, $language),
            $this->engine->lexis->concepts(),
        ));
    }

    /** @return array{status: int, type: string, body: string} */
    private function chat(array $post): array
    {
        $number = (int) ($post['lesson'] ?? 1);
        $prompt = $this->engine->promptForLesson($number);

        return $this->html('Chat', Views::chat($this->base, $prompt, $number, $this->engine));
    }

    /** @return array{status: int, type: string, body: string} */
    private function path(): array
    {
        return $this->html('Path', Views::path($this->base, $this->engine));
    }

    /** @return array{status: int, type: string, body: string} */
    private function asset(string $name): array
    {
        $file = __DIR__ . '/../../showroom/assets/' . basename($name);
        if (!is_file($file)) {
            return $this->notFound();
        }
        $types = ['css' => 'text/css; charset=utf-8', 'js' => 'text/javascript; charset=utf-8', 'svg' => 'image/svg+xml'];
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        return [
            'status' => 200,
            'type' => $types[$extension] ?? 'application/octet-stream',
            'body' => (string) file_get_contents($file),
        ];
    }

    /** @return array{status: int, type: string, body: string} */
    private function notFound(): array
    {
        return ['status' => 404, 'type' => 'text/html; charset=utf-8', 'body' => Views::notFound($this->base)];
    }

    /** @param array<string, mixed> $data */
    private function json(array $data): array
    {
        return ['status' => 200, 'type' => 'application/json', 'body' => json_encode($data, JSON_UNESCAPED_UNICODE) ?: '{}'];
    }

    /** @return array{status: int, type: string, body: string} */
    private function html(string $title, string $body, int $status = 200): array
    {
        return ['status' => $status, 'type' => 'text/html; charset=utf-8', 'body' => Views::page($title, $body, $this->base)];
    }
}
