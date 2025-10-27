<?php

namespace App\Config;

class ProtectedRoutes
{
    public const ROUTES = [
        '#^/api/books(/.*)?$#',
        '#^/api/statuses(/.*)?$#',
        '#^/api/shelves(/.*)?$#',
        '#^/api/authors(/.*)?$#',
        '#^/api/batch-authors$#',
        '#^/api/notes(/.*)?$#',
        '#^/api/quotes(/.*)?$#',
        '#^/api/settings(/.*)?$#',
        '#^/api/reading-progress(/.*)?$#',
        '#^/api/books/\d+/cover$#',
        '#^/api/books/\d+/epub$#',
        '#^/api/books/\d+/status$#',
        '#^/api/reorder-statuses$#',
    ];
}