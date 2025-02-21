# Dejan's library
A short Symfony project made using TDD methodologies, with Docker integration

## Setup and run

### Requirements
Git
Docker runtime

### Setup
Clone into your local folder

> git clone https://github.com/dmisljen/library.git

Build and run containers

> docker compose build --no-cache --pull && docker compose up -d

Note: If you're starting the containers for the first time, it will take a few minutes to pull in all the dependencies and spin up the server and database.

## Test

### Run test from local console in project root
> php bin/phpunit tests/


### Or use Postman or your favourite tool to hit the available endpoints:

    - Route 'api_book_index'        => '/api/book'
    - Route 'api_book_show'         => '/api/book/{id}'
    - Route 'api_book_show_by_isbn' => '/api/book/find-by-isbn/{isbn}'
    - Route 'api_book_new'          => '/api/book/new'
    - Route 'api_book_update'       => '/api/book/{id}/update'
    - Route 'api_book_delete'       => '/api/book/{id}/delete'


### Or just a basic test - open the project URL in your browser. 

[https://localhost:443/api/book](https://localhost:443/api/book)

You will get an empty books array, make sure to hit https and accept the unsafe connection.
