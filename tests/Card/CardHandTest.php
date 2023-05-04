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
    public function testCreateObjectEmptyArguments()
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
    public function testAddCardGetValue()
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
}