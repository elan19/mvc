<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LibraryController extends AbstractController
{
    #[Route('/library', name: 'library_home')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig', [
            'controller_name' => 'LibraryController',
        ]);
    }

    /**
     * @Route("/books/new", name="book_new")
     */
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
}
