<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use App\Tests\DatabasePrimer;
use App\Entity\Book;

final class BookControllerTest extends WebTestCase
{

    private $entityManager;
    private $client;

    protected function setUp() : void {
        
        $this->client = static::createClient();
        $kernel = self::$kernel;

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown() : void {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

        /** @test */
        public function book_index_returns_results() {
        // Setup
        $book = new Book();
        $book->setIsbn('9780261102385');
        $book->setTitle('The Lord of the Rings (3 Book Box set)');
        $book->setAuthor('John R. R. Tolkien');
        $book->setPublishedYear(1990);
        $book->setGenre('Epic fantasy');

        $this->entityManager->persist($book);
        $this->entityManager->flush($book);

        // Ensure the database changes are committed
        $this->entityManager->clear();

        // Do something   
        $this->client->enableProfiler();

        $this->client->request('GET', '/api/book');
        $response = $this->client->getResponse();

        // Assertion
        self::assertResponseIsSuccessful();
        $responseData = json_decode($response->getContent(), true);
        self::assertCount(1, $responseData['books']);
        self::assertEquals('9780261102385', $responseData['books'][0]['isbn']);
    }

    /** @test */
    public function find_book_by_id() {
        // Setup
        $book = new Book();
        $book->setIsbn('9780261102385');
        $book->setTitle('The Lord of the Rings (3 Book Box set)');
        $book->setAuthor('John R. R. Tolkien');
        $book->setPublishedYear(1990);
        $book->setGenre('Epic fantasy');

        $this->entityManager->persist($book);
        $this->entityManager->flush($book);

        $book_id = $book->getID();

        // Do something   
        $this->client->request('GET', '/api/book/'.$book_id);
        $response = $this->client->getResponse();

        // Assertion
        self::assertResponseIsSuccessful();
        $responseData = json_decode($response->getContent(), true);
        self::assertCount(1, $responseData);
        self::assertEquals('9780261102385', $responseData['book']['isbn']);
    }

    /** @test */
    public function find_book_by_isbn() {
        // Setup
        $book = new Book();
        $book->setIsbn('9780261102385');
        $book->setTitle('The Lord of the Rings (3 Book Box set)');
        $book->setAuthor('John R. R. Tolkien');
        $book->setPublishedYear(1990);
        $book->setGenre('Epic fantasy');

        $this->entityManager->persist($book);
        $this->entityManager->flush($book);

        // Do something   
        $this->client->request('GET', '/api/book/find-by-isbn/9780261102385');
        $response = $this->client->getResponse();

        // Assertion
        self::assertResponseIsSuccessful();
        $responseData = json_decode($response->getContent(), true);
        self::assertCount(1, $responseData);
        self::assertEquals('9780261102385', $responseData['book']['isbn']);
    }

    /** @test */
    public function create_book() {
        // Setup
        $requestParams = [
            'title' => 'The Lord of the Rings - 3 Book Box set',
            'author' => 'John R.R. Tolkien',
            'isbn' => '978-3-16-148410-0',
            'published-year' => 1989,
            'genre' => 'Epic fantasy 2'
        ];

        // Do something
        $this->client->request('POST', '/api/book/new', $requestParams);

        $bookRepository = $this->entityManager->getRepository(Book::class);
        $storedBook = $bookRepository->findOneBy(['isbn' => '978-3-16-148410-0']);

        // Assertion
        self::assertResponseIsSuccessful();

        $this->assertEquals('The Lord of the Rings - 3 Book Box set', $storedBook->getTitle());
        $this->assertEquals('John R.R. Tolkien', $storedBook->getAuthor());
        $this->assertEquals('978-3-16-148410-0', $storedBook->getIsbn());
        $this->assertEquals(1989, $storedBook->getPublishedYear());
        $this->assertEquals('Epic fantasy 2', $storedBook->getGenre());
    }

    /** @test */
    public function edit_book() {
        // Setup
        // create a book
        $book = new Book();
        $book->setIsbn('9780261102385');
        $book->setTitle('The Lord of the Rings (3 Book Box set)');
        $book->setAuthor('John R. R. Tolkien');
        $book->setPublishedYear(1990);
        $book->setGenre('Epic fantasy');
        
        $this->entityManager->persist($book);
        $this->entityManager->flush($book);

        $book_id = $book->getID();
        
        // params for edit
        $requestParams = [
            'title' => 'The Hobbit & The Lord of the Rings Boxed Set: The Hobbit, The Fellowship of the Ring, The Two Towers, The Return of the King',
            'author' => 'J. R. R. Tolkien',
            'isbn' => '978-0261103566',
            'published-year' => 2011,
            'genre' => 'Epic fantasy 2'
        ];

        // Do something
        $this->client->request('PUT', '/api/book/' . $book_id . '/update', $requestParams);

        $bookRepository = $this->entityManager->getRepository(Book::class);
        $storedBook = $bookRepository->find($book_id);

        // Assertion
        self::assertResponseIsSuccessful();

        $this->assertEquals('The Hobbit & The Lord of the Rings Boxed Set: The Hobbit, The Fellowship of the Ring, The Two Towers, The Return of the King', $storedBook->getTitle());
        $this->assertEquals('J. R. R. Tolkien', $storedBook->getAuthor());
        $this->assertEquals('978-0261103566', $storedBook->getIsbn());
        $this->assertEquals(2011, $storedBook->getPublishedYear());
        $this->assertEquals('Epic fantasy 2', $storedBook->getGenre());
    }

    /** @test */
    public function delete_book() {
        // Setup
        // create a book
        $book = new Book();
        $book->setIsbn('9780261102385');
        $book->setTitle('The Lord of the Rings (3 Book Box set)');
        $book->setAuthor('John R. R. Tolkien');
        $book->setPublishedYear(1990);
        $book->setGenre('Epic fantasy');
        
        $this->entityManager->persist($book);
        $this->entityManager->flush($book);

        $book_id = $book->getID();

        // Do something
        $this->client->request('DELETE', '/api/book/' . $book_id . '/delete');

        $response = $this->client->getResponse();

        $bookRepository = $this->entityManager->getRepository(Book::class);
        $storedBook = $bookRepository->find($book_id);

        // Assertion
        self::assertResponseIsSuccessful();

        $this->assertEquals(null, $storedBook);
    }
}
