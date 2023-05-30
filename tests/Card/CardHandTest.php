<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class CardHandTest extends TestCase
{
    /**
     * Construct object with arguments 5 and H
     */
    public function testCreateObjectEmptyArguments(): void
    {
        $hand = new CardHand();
        $this->assertInstanceOf("App\Card\CardHand", $hand);

        $res = $hand->getCards();
        $exp = [];
        $this->assertEquals($exp, $res);
    }

    /**
     * Test to add a card and get the hands value
     */
    public function testAddCardGetValue(): void
    {
        $hand = new CardHand();
        $this->assertInstanceOf("App\Card\CardHand", $hand);

        $card = new CardGraphic("5", "H");
        $hand->addCard($card);
        $res = $hand->getHandValue();
        $exp = 5;
        $this->assertEquals($exp, $res);
        $card1 = new CardGraphic("10", "H");
        $card2 = new CardGraphic("A", "H");
        $hand->addCard($card1);
        $hand->addCard($card2);
        $res = $hand->getHandValue();
        $exp = 16;
        $this->assertEquals($exp, $res);
    }

    /**
     * Test to check isBust function
     */
    public function testisBust(): void
    {
        $hand = new CardHand();
        $card = new CardGraphic("10", "H");
        $card2 = new CardGraphic("J", "H");
        $card3 = new CardGraphic("5", "H");
        $hand->addCard($card);
        $hand->addCard($card2);
        $hand->addCard($card3);
        $this->assertEquals($hand->isBust(), true);
    }

    /**
     * Test to check isStand and Stand function
     */
    public function testStandisStand(): void
    {
        $hand = new CardHand();
        $hand->stand();
        $this->assertEquals($hand->isStand(), true);
    }

    /**
     * Test to check get and setBet function
     */
    public function testGetSetBet(): void
    {
        $hand = new CardHand();
        $hand->setBet(20);
        $this->assertEquals($hand->getBet(), 20);
    }

    /**
     * Test to check totalMoney function
     */
    public function testgetTotalMoney(): void
    {
        $hand = new CardHand();
        $this->assertEquals($hand->getTotalMoney(), 50);
    }

    /**
     * Test to check updateTotalMoney function
     */
    public function testupdateTotalMoney(): void
    {
        $hand = new CardHand();
        $hand->updateTotalMoney(25);
        $this->assertEquals($hand->getTotalMoney(), 75);
    }

    /**
     * Test to check updateTotalMoney function
     */
    public function testResetHand(): void
    {
        $hand = new CardHand();
        $hand->stand();
        $hand->setBet(25);
        $hand->resetHand();
        $this->assertEquals($hand->isStand(), false);
    }
}