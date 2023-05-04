<?php

namespace App\Card;

/**
 * Represents a hand of playing cards.
 */
class CardHand
{
    /**
     * An array of CardGraphic objects representing the cards in the hand.
     *
     * @var CardGraphic[]
     */
    private array $cards;

    /**
     * Initializes an empty CardHand object.
     */
    public function __construct()
    {
        $this->cards = [];
    }

    /**
     * Adds a CardGraphic object to the hand.
     *
     * @param CardGraphic $card The CardGraphic object to add to the hand.
     *
     * @return void
     */
    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Gets an array of CardGraphic objects representing the cards in the hand.
     *
     * @return CardGraphic[] An array of CardGraphic objects representing the cards in the hand.
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Calculates the value of the hand according to the rules of blackjack.
     *
     * @return float The value of the hand.
     */
    public function getHandValue(): float
    {
        $cards = $this->getCards();
        $sum = 0;
        $numAces = 0;

        foreach ($cards as $card) {
            $rank = $card->getRank();
            $cardValue = is_numeric($rank) ? $rank : ($rank == 'A' ? 11 : 10);
            $sum += $cardValue;
            if ($rank == 'A') {
                $numAces++;
            }
        }

        // Deduct 10 for each Ace if the sum is over 21
        while ($numAces > 0 && $sum > 21) {
            $sum -= 10;
            $numAces--;
        }

        return $sum;
    }
}
