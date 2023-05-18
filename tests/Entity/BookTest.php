<?php

namespace App\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Book.
 */
class BookTest extends TestCase
{
    /**
     * Construct object
     */
    public function testCreateObject(): void
    {
        $book = new Book();
        $this->assertInstanceOf("App\Entity\Book", $book);
    }

    public function testGetSetTitle(): void
    {
        $book = new Book();
        $book->setTitle("Kalle med Hajar");
        $this->assertEquals($book->getTitle(), "Kalle med Hajar");
    }

    public function testGetSetISBN(): void
    {
        $book = new Book();
        $book->setISBN(5565);
        $this->assertEquals($book->getISBN(), 5565);
    }

    public function testGetSetAuthor(): void
    {
        $book = new Book();
        $book->setAuthor("Kalle Björk");
        $this->assertEquals($book->getAuthor(), "Kalle Björk");
    }

    public function testGetSetPicture(): void
    {
        $book = new Book();
        $book->setPicture("img/test");
        $this->assertEquals($book->getPicture(), "img/test");
    }

    public function testID(): void
    {
        $book = new Book();
        $this->assertEquals($book->getId(), 0);
    }
}