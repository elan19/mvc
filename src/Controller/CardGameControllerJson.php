<?php

namespace App\Controller;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\DeckOfCards;
use Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CardGameControllerJson
{
    #[Route("/api/deck", name: "api_deck", methods: ['GET'])]
    public function jsonDeck(SessionInterface $session): Response
    {
        if ($session->has("deck") == FALSE) {
            $deck = new DeckOfCards();
            $session->set("deck", $deck);
        }
        $deck = $session->get("deck");
        $cards = [];
        if ($deck instanceof DeckOfCards) {
            $deck->sort();
            $cards = $deck->getCards();
        }

        $cardSymbol = array();

        foreach ($cards as $card) {
            array_push($cardSymbol, $card->getSymbol());
        }

        $data = [
            'cards' => $cardSymbol,
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/shuffle", name:"api_shuffle", methods: ['POST'])]
    public function jsonShuffle(SessionInterface $session): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $cards = $deck->getCards();
        $session->set("deck", $deck);
        $cards = $deck->getCards();

        $cardSymbol = array();

        foreach ($cards as $card) {
            array_push($cardSymbol, $card->getSymbol());
        }

        $data = [
            'cards' => $cardSymbol,
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/draw", name:"api_draw", methods: ['POST'])]
    public function jsonDraw(SessionInterface $session): Response
    {
        if ($session->has("deck") == FALSE) {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $session->set("deck", $deck);
        }
        $data = [];
        $deck = $session->get("deck");
        if ($deck instanceof DeckOfCards) {
            $cardSymbol = "";
            if ($deck->cardsLeft() !== 0) {
                $card = $deck->drawCard();
                $session->set("lastCard", $card);
            }
            if ($deck->cardsLeft() > 0){
                $card = $session->get("lastCard");
                if($card instanceof CardGraphic) {
                    $cardSymbol = $card->getSymbol();
    
                    $data = [
                        'cards' => $cardSymbol,
                        'cards-left' => $deck->cardsLeft()
                    ];
    
                    $response = new JsonResponse($data);
                    $response->setEncodingOptions(
                        $response->getEncodingOptions() | JSON_PRETTY_PRINT
                    );
                    return $response;
                }
            }
        }
        $data = [
            'cards' => "Error in session",
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/draw/{num<\d+>}", name:"api_draws", methods: ['POST'])]
    public function jsonDraws(SessionInterface $session, int $num): Response
    {
        if ($session->has("deck") == FALSE) {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $session->set("deck", $deck);
        }
        $deck = $session->get("deck");
        if ($deck instanceof DeckOfCards) {
            if ($num > $deck->cardsLeft()) {
                throw new Exception("Can not show more cards than exists in the deck!");
            }
        }

        $cards = [];
        for ($i = 0; $i < $num; $i++) {
            if ($deck instanceof DeckOfCards) {
                $cards[$i] = $deck->drawCard();
            }
        }

        $cardSymbol = array();

        foreach ($cards as $card) {
            if ($card instanceof CardGraphic) {
                array_push($cardSymbol, $card->getSymbol());
            }
        }

        $data = [];

        if ($deck instanceof DeckOfCards) {
            $data = [
                'cards' => $cardSymbol,
                'cards-left' => $deck->cardsLeft()
            ];
        }

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }
}
