<?php

namespace App\Card;

class CardHand
{
    /**
    * @var Card[]
    */
    private array $cards;

    public function __construct()
    {
        $this->cards = [];
    }

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    /**
    * @return Card[]
    */
    public function getCards(): array
    {
        return $this->cards;
    }
}
