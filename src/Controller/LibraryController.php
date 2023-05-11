<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class LibraryController extends AbstractController
{
    #[Route('/library', name: 'library_home')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig', [
            'controller_name' => 'LibraryController',
        ]);
    }

    #[Route('/library/add', name: 'library_add')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('library_home');
        }

        return $this->render('library/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/library/book/{id}', name: 'library_show')]
    public function show(Book $book): Response
    {

        return $this->render('library/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/books', name: 'library_showAll')]
    public function showAll(EntityManagerInterface $entityManager): Response
    {
        $bookRepository = $entityManager->getRepository(Book::class);
        $books = $bookRepository->findAll();

        return $this->render('library/show_many.html.twig', ['books' => $books,]);
    }

    #[Route('/library/book/{id}/edit', name: 'library_edit', methods: ['POST'])]
    public function edit(Book $book, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('library_show', ['id' => $book->getId()]);
        }

        return $this->render('library/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/library/book/{id}/delete', name: 'library_delete', methods: ['POST'])]
    public function delete(Book $book, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($book);
        $entityManager->flush();
        return $this->redirectToRoute('library_home');
    }

    #[Route('/api/library/books', name: 'api_library_books', methods: ['GET'])]
    public function apiBooks(EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $entityManager->getRepository(Book::class)->findAll();
        $data = [];

        foreach ($books as $book) {
            $data[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'ISBN' => $book->getISBN(),
                'picture' => $book->getPicture(),
            ];
        }

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route('/api/library/book/{isbn}', name: 'api_library_book', methods: ['GET'])]
    public function apiBook(string $isbn, EntityManagerInterface $entityManager): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)
            ->findOneBy(['ISBN' => $isbn]);

        if (!$book) {
            throw $this->createNotFoundException('The book with ISBN ' . $isbn . ' does not exist.');
        }

        $data = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'ISBN' => $book->getISBN(),
            'picture' => $book->getPicture(),
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }
}
