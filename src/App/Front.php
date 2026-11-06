<?php

declare(strict_types=1);

namespace Interface\App;

/** The front controller the built-in server calls for every request. */
final class Front
{
    public static function run(): void
    {
        $router = new Router();
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = $router->base();

        if ($base !== '/' && str_starts_with($path, $base)) {
            $path = '/' . substr($path, strlen($base));
        }

        $result = $router->handle($_SERVER['REQUEST_METHOD'] ?? 'GET', $path, $_POST);

        http_response_code($result['status']);
        header('Content-Type: ' . $result['type']);
        header('X-Content-Type-Options: nosniff');
        echo $result['body'];
    }
}
