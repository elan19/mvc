<?php

namespace App\Card;

use Exception;

class DeckOfCards
{
    /**
    * @var CardGraphic[]
    */
    private array $cards;

    public function __construct()
    {
        $this->cards = [];

        // Lägg till kort i kortleken
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
        $suits = ['H', 'D', 'C', 'S'];
        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $card = new CardGraphic($rank, $suit);
                $this->cards[] = $card;
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    public function deal(int $numCards): CardHand
    {
        $hand = new CardHand();
        for ($i = 0; $i < $numCards; $i++) {
            $card = array_shift($this->cards);
            if ($card !== null) {
                $hand->addCard($card);
            }
        }
        return $hand;
    }

    public function sort(): void
    {
        $suitsOrder = ['H', 'D', 'C', 'S'];

        $rankOrder = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        $sortedDeck = [];

        foreach ($suitsOrder as $suit) {
            foreach ($rankOrder as $rank) {
                foreach ($this->cards as $key => $card) {
                    if ($card->getRank() == $rank && $card->getSuit() == $suit) {
                        $sortedDeck[] = $card;
                        unset($this->cards[$key]);
                        break;
                    }
                }
            }
        }
        $this->cards = $sortedDeck;
    }

    /**
    * @return CardGraphic[]
    */
    public function getCards(): array
    {
        return $this->cards;
    }

    public function cardsLeft(): int
    {
        return count($this->cards);
    }

    public function drawCard(): CardGraphic
    {
        if(empty($this->cards)) {
            throw new Exception('Empty deck.');
        }

        return array_shift($this->cards);
    }
}
