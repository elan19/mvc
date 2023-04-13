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

class CardGameController extends AbstractController
{
    #[Route("/card", name: "card_start")]
    public function home(SessionInterface $session): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $session->set("deck", $deck);
        return $this->render('cardGame/home.html.twig');
    }

    #[Route("/card/deck", name: "card_deck")]
    public function deck(SessionInterface $session): Response
    {
        //$card = new CardGraphic(5, "H");
        /*$deck = new DeckOfCards();
        $deck->sort();
        $cards = $deck->getCards();*/

        if ($session->has("deck")) {
            $deck = $session->get("deck");
        } else {
            $deck = new DeckOfCards();
            $session->set("deck", $deck);
        }
        $deck->sort();
        $cards = $deck->getCards();
        return $this->render('cardGame/deck.html.twig', ['cards' => $cards,]);
    }

    #[Route("/card/deck/shuffle", name: "shuffle_deck")]
    public function shuffle(SessionInterface $session): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $cards = $deck->getCards();
        $session->set("deck", $deck);
    return $this->render('cardGame/shuffle.html.twig', ['cards' => $cards,]);
    }

    #[Route("/card/deck/draw", name: "draw_card")]
    public function draw(SessionInterface $session): Response
    {
        if ($session->has("deck")) {
            $deck = $session->get("deck");
        } else {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $session->set("deck", $deck);
        }
        if ($deck->cardsLeft() !== 0) {
            $card = $deck->drawCard();
            $session->set("lastCard", $card);
        } else {
            $card = $session->get("lastCard");
        }
    return $this->render('cardGame/draw.html.twig', ['deck' => $deck, 'card' => $card,]);
    }

    #[Route("/card/deck/draw/{num<\d+>}", name: "draw_cards")]
    public function drawCards(SessionInterface $session, int $num): Response
    {
        if ($session->has("deck")) {
            $deck = $session->get("deck");
        } else {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $session->set("deck", $deck);
        }
        if ($num > $deck->cardsLeft()) {
            throw new \Exception("Can not show more cards than exists in the deck!");
        }

        $cards = [];
        for ($i = 0; $i < $num; $i++) {
            $cards[$i] = $deck->drawCard();
        }
    return $this->render('cardGame/draw_many.html.twig', ['deck' => $deck, 'cards' => $cards,]);
    }
}