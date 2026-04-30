<?php

namespace App\Config;

class ProtectedRoutes
{
    public const ROUTES = [
        '#^/api/books(/.*)?$#',
        '#^/api/book(/.*)?$#',
        '#^/api/book-update$#',
        '#^/api/statuses(/.*)?$#',
        '#^/api/shelves(/.*)?$#',
        '#^/api/authors(/.*)?$#',
        '#^/api/batch-authors$#',
        '#^/api/notes(/.*)?$#',
        '#^/api/quotes(/.*)?$#',
        '#^/api/settings(/.*)?$#',
        '#^/api/reading-progress(/.*)?$#',
        '#^/api/cover-book/(\d+)#',
        '#^/api/books/\d+/epub$#',
        '#^/api/book-status(/.*)?$#',
        '#^/api/reorder-statuses$#',
    ];
}