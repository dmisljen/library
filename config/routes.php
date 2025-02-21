<?php

use App\Controller\BookController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('api_book_index', '/api/book')
        ->controller([BookController::class, 'index'])
        ->methods(['GET'])
    ;
    $routes->add('api_book_show', '/api/book/{id}')
        ->controller([BookController::class, 'show'])
        ->requirements(['id' => '\d+'])
        ->methods(['GET'])
    ;
    $routes->add('api_book_show_by_isbn', '/api/book/find-by-isbn/{isbn}')
        ->controller([BookController::class, 'show_by_isbn'])
        ->requirements(['isbn' => '\d+'])
        ->methods(['GET'])
    ;
    $routes->add('api_book_new', '/api/book/new')
        ->controller([BookController::class, 'new'])
        ->methods(['POST'])
    ;
    $routes->add('api_book_update', '/api/book/{id}/update')
        ->controller([BookController::class, 'update'])
        ->requirements(['id' => '\d+'])
        ->methods(['PUT'])
    ;
    $routes->add('api_book_delete', '/api/book/{id}/delete')
        ->controller([BookController::class, 'delete'])
        ->methods(['DELETE'])
    ;
};