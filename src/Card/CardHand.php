<?php

namespace App\Card;

class CardHand
{
    /**
    * @var CardGraphic[]
    */
    private array $cards;

    public function __construct()
    {
        $this->cards = [];
    }

    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
    }

    /**
    * @return CardGraphic[]
    */
    public function getCards(): array
    {
        return $this->cards;
    }

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
