<?php

namespace App\Card;

class DeckOfCards
{
    private $cards;

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

    public function shuffle()
    {
        shuffle($this->cards);
    }

    public function deal($numCards)
    {
        $hand = new CardHand();
        for ($i = 0; $i < $numCards; $i++) {
            $card = array_shift($this->cards);
            $hand->addCard($card);
        }
        return $hand;
    }

    public function sort()
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

    public function getCards()
    {
        return $this->cards;
    }

    public function cardsLeft()
    {
        return count($this->cards);
    }

    public function drawCard()
    {
        if(empty($this->cards)) {
            return null;
        }

        return array_shift($this->cards);
    }
}