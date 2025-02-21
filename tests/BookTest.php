<?php 

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Tests\DatabasePrimer;
use App\Entity\Book;


class BookTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp() : void {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown() : void {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testIfTestingWorks() {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_book_can_be_created() {
        // Setup
        $book = new Book();
        $book->setIsbn('9780261102385');
        $book->setTitle('The Lord of the Rings (3 Book Box set)');
        $book->setAuthor('John R. R. Tolkien');
        $book->setPublishedYear(1990);
        $book->setGenre('Epic fantasy');

        $this->entityManager->persist($book);

        // Do something
        $this->entityManager->flush($book);

        $bookRepository = $this->entityManager->getRepository(Book::class);

        $storedBook = $bookRepository->findOneBy(['isbn' => '9780261102385']);

        // Assertion
        $this->assertEquals('9780261102385', $storedBook->getIsbn());
        $this->assertEquals('The Lord of the Rings (3 Book Box set)', $storedBook->getTitle());
        $this->assertEquals('John R. R. Tolkien', $storedBook->getAuthor());
        $this->assertEquals(1990, $storedBook->getPublishedYear());
        $this->assertEquals('Epic fantasy', $storedBook->getGenre());
    }
}
