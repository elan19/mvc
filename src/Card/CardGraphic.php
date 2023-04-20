<?php

namespace App\Card;

class CardGraphic extends Card
{
    public function __construct(string $rank, string $suit)
    {
        parent::__construct($rank, $suit);
    }

    public function getSymbol(): ?string
    {
        // Använd utf-8-tecken för att representera kortet
        $suits = [
            'H' => '♥',
            'D' => '♦',
            'C' => '♣',
            'S' => '♠',
        ];

        $ranks = [
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
            'J' => 'J',
            'Q' => 'Q',
            'K' => 'K',
            'A' => 'A',
        ];

        return $suits[$this->getSuit()] . $ranks[$this->getRank()];
    }
}
