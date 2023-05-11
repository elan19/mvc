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

class LibraryControllerJson
{
    /*#[Route('/api/library/books', name: 'api_library_books', methods: ['GET'])]
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
    }*/
}
