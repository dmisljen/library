<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookRepository;
use App\Entity\Book;

final class BookController extends AbstractController
{
     /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Route 'api_book_index', '/api/book'
     */
    public function index(BookRepository $repository): JsonResponse
    {
        $books = [
            'books' => $repository->findAll()
        ];

        $jsonContent = $this->serializer->serialize($books, 'json');

        return JsonResponse::fromJsonString($jsonContent);  
    }

    /**
     * Route 'api_book_show', '/api/book/{id}'
     */
    public function show(string $id, BookRepository $repository): JsonResponse
    {
        $books = [
            'book' => $repository->find($id)
        ];

        $jsonContent = $this->serializer->serialize($books, 'json');

        return JsonResponse::fromJsonString($jsonContent);  
    }

    /**
     * Route * 'api_book_show_by_isbn', '/api/book/find-by-isbn/{isbn}'
     */
    public function show_by_isbn(string $isbn, BookRepository $repository): JsonResponse
    {
        
        $book = $repository->findOneBy(['isbn' => $isbn]);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $jsonContent = [
            'book' => $book
        ];
        $jsonContent = $this->serializer->serialize($jsonContent, 'json');

        return JsonResponse::fromJsonString($jsonContent);
    }

    /**
     * Route 'api_book_new', '/api/book/new'
     */
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {

        $constraint = new Assert\Collection([
            'title' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 255,
                    'minMessage' => 'The title must be at least {{ limit }} characters long',
                    'maxMessage' => 'The title cannot be longer than {{ limit }} characters',
                ])
            ],
            'author' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 100,
                    'minMessage' => 'The author name must be at least {{ limit }} characters long',
                    'maxMessage' => 'The author name cannot be longer than {{ limit }} characters',
                ])
            ],
            'isbn' => [
                new Assert\NotBlank(),
                new Assert\Isbn([
                    'message' => 'This value is not a valid ISBN-10.',
                ]),
            ],
            'published-year' => new Assert\Range([
                'min' => 0,
                'max' => date("Y"),
                'notInRangeMessage' => 'Published year must be between {{ min }} and {{ max }}.',
            ]),
            'genre' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 50,
                    'minMessage' => 'The genre must be at least {{ limit }} characters long',
                    'maxMessage' => 'The genre cannot be longer than {{ limit }} characters',
                ])
            ],
        ]);

        $validationResult = $validator->validate(
            $request->request->all(),
            $constraint
        );

        if (count($validationResult) > 0) {
            return JsonResponse::fromJsonString($this->serializer->serialize($validationResult, 'json'));
        }

        $book = new Book();
        $book->setTitle($request->request->get('title'));
        $book->setAuthor($request->request->get('author'));
        $book->setIsbn($request->request->get('isbn'));
        $book->setPublishedYear($request->request->get('published-year'));
        $book->setGenre($request->request->get('genre'));

        $entityManager->persist($book);
        $entityManager->flush();

        return $this->json([
            'message' => 'Book created successfully!',
            'book' => $book,
        ]);
    }

    /**
     * Route 'api_book_update', '/api/book/{id}/update'
     */
    public function update(string $id, Request $request, BookRepository $repository, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $constraint = new Assert\Collection([
            'title' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 255,
                    'minMessage' => 'The title must be at least {{ limit }} characters long',
                    'maxMessage' => 'The title cannot be longer than {{ limit }} characters',
                ])
            ],
            'author' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 100,
                    'minMessage' => 'The author name must be at least {{ limit }} characters long',
                    'maxMessage' => 'The author name cannot be longer than {{ limit }} characters',
                ])
            ],
            'isbn' => [
                new Assert\NotBlank(),
                new Assert\Isbn([
                    'message' => 'This value is not a valid ISBN-10.',
                ]),
            ],
            'published-year' => new Assert\Range([
                'min' => 0,
                'max' => date("Y"),
                'notInRangeMessage' => 'Published year must be between {{ min }} and {{ max }}.',
            ]),
            'genre' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 3,
                    'max' => 50,
                    'minMessage' => 'The genre must be at least {{ limit }} characters long',
                    'maxMessage' => 'The genre cannot be longer than {{ limit }} characters',
                ])
            ],
        ]);

        $validationResult = $validator->validate(
            $request->request->all(),
            $constraint
        );

        if (count($validationResult) > 0) {
            return JsonResponse::fromJsonString($this->serializer->serialize($validationResult, 'json'));
        }

        $book = $repository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $book->setTitle($request->request->get('title'));
        $book->setAuthor($request->request->get('author'));
        $book->setIsbn($request->request->get('isbn'));
        $book->setPublishedYear($request->request->get('published-year'));
        $book->setGenre($request->request->get('genre'));

        $entityManager->flush();

        $response = [
            'message' => 'Book updated successfully!',
            'book' => $book
        ];

        $jsonContent = $this->serializer->serialize($response, 'json');

        return JsonResponse::fromJsonString($jsonContent);
    }

    /**
     * 'api_book_delete', '/api/book/{id}/delete'
     */
    public function delete(string $id, BookRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $book = $repository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $entityManager->remove($book);
        $entityManager->flush();

        $response = [
            'message' => 'Book deleted successfully!'
        ];

        $jsonContent = $this->serializer->serialize($response, 'json');

        return JsonResponse::fromJsonString($jsonContent);
    }
}
