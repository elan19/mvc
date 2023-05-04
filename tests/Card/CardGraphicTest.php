<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Card.
 */
class CardGraphicTest extends TestCase
{
    /**
     * Construct object with arguments 5 and H and get the symbols
     */
    public function testCreateObject()
    {
        $card = new CardGraphic("5", "H");
        $this->assertInstanceOf("App\Card\CardGraphic", $card);

        $res = $card->getSymbol();
        $exp = "♥5";
        $this->assertEquals($exp, $res);
    }
}