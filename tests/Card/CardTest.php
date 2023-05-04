<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class CardTest extends TestCase
{
    /**
     * Construct object with arguments 5 and H
     */
    public function testCreateObject()
    {
        $card = new Card("5", "H");
        $this->assertInstanceOf("App\Card\Card", $card);

        $res = $card->getRank();
        $exp = "5";
        $this->assertEquals($exp, $res);

        $res = $card->getSuit();
        $exp = "H";
        $this->assertEquals($exp, $res);
    }
}