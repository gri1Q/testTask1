<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::view('/docs', 'swagger'); // UI на /docs

Route::get('/api-docs/{path?}', function (?string $path = 'index.yaml') {
    $base = base_path('api-docs');
    $full = realpath($base . DIRECTORY_SEPARATOR . $path);

    // Защита от обхода директорий
    if ($full === false || !str_starts_with($full, realpath($base))) {
        abort(404);
    }
    if (!File::exists($full)) {
        abort(404);
    }

    $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'yaml', 'yml' => 'text/yaml',
        'json' => 'application/json',
        'js' => 'application/javascript',
        'css' => 'text/css',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        default => 'text/plain',
    };

    return Response::file($full, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline',
    ]);
})->where('path', '.*');
