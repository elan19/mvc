<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class DeckOfCard.
 */
class DeckOfCardsTest extends TestCase
{
    /**
     * Construct object
     */
    public function testCreateObjectEmptyArgument()
    {
        $deck = new DeckOfCards();
        $this->assertInstanceOf("App\Card\DeckOfCards", $deck);
    }

    /**
     * Test shuffle function
     */
    public function testShuffle()
    {
        $deck = new DeckOfCards();
        $deck->sort();
        $deck2 = new DeckOfCards();
        $deck2->shuffle();
        $this->assertNotEquals($deck, $deck2);
    }

    /**
     * Test deal function
     */
    public function testDeal()
    {
        $deck = new DeckOfCards();
        $deck->sort();
        $hand = $deck->deal(2);
        $res = $hand->getHandValue();
        $exp = 5;
        $this->assertEquals($exp, $res);
    }

    /**
     * Test getCards function
     */
    public function testGetCards()
    {
        $deck = new DeckOfCards();
        $deck->sort();
        $res = $deck->getCards();
        $exp = [];
        $this->assertNotEquals($exp, $res);
    }

    /**
     * Test cardsLeft function
     */
    public function testCardsLeft()
    {
        $deck = new DeckOfCards();
        $res = $deck->cardsLeft();
        $exp = 52;
        $this->assertEquals($exp, $res);
    }

    /**
     * Test to draw a card function
     */
    public function testDrawCard()
    {
        $deck = new DeckOfCards();
        $deck->sort();
        $card = $deck->drawCard();
        $res = $card->getSymbol();
        $exp = "♥2";
        $this->assertEquals($exp, $res);
    }
}