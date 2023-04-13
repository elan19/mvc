<?php

namespace App\Controller;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\DeckOfCards;

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
        if ($session->has("deck")) {
            $deck = $session->get("deck");
        } else {
            $deck = new DeckOfCards();
            $session->set("deck", $deck);
        }
        $deck->sort();
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

    #[Route("/api/deck/shuffle", name:"api_shuffle", methods: ['POST'])]
    public function jsonShuffle(SessionInterface $session): Response
    {
        if ($session->has("deck")) {
            $deck = $session->get("deck");
        } else {
            $deck = new DeckOfCards();
            $session->set("deck", $deck);
        }
        $deck->shuffle();
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
}