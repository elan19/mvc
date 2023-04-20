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

        if ($session->has("deck") == FALSE) {
            $deck = new DeckOfCards();
            $session->set("deck", $deck);
        }
        $deck = $session->get("deck");
        if ($deck instanceof DeckOfCards) {
            $deck->sort();
            $cards = $deck->getCards();
            return $this->render('cardGame/deck.html.twig', ['cards' => $cards,]);
        }
        return $this->render('cardGame/deck.html.twig');
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
        if ($session->has("deck") == FALSE) {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $session->set("deck", $deck);
        }
        $deck = $session->get("deck");
        if ($deck instanceof DeckOfCards) {
            $card = [];
            if ($deck->cardsLeft() !== 0) {
                $card = $deck->drawCard();
                $session->set("lastCard", $card);
            }
            if ($deck->cardsLeft() > 0){
                $card = $session->get("lastCard");
            }
            return $this->render('cardGame/draw.html.twig', ['deck' => $deck, 'card' => $card,]);
        }
        return $this->render('cardGame/draw.html.twig', ['deck' => $deck,]);
    }

    #[Route("/card/deck/draw/{num<\d+>}", name: "draw_cards")]
    public function drawCards(SessionInterface $session, int $num): Response
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
        return $this->render('cardGame/draw_many.html.twig', ['deck' => $deck, 'cards' => $cards,]);
    }

    #[Route("/game", name: "game_home")]
    public function game(): Response
    {
        //$deck = new DeckOfCards();
        //$deck->shuffle();
        //$session->set("deck", $deck);
        return $this->render('cardGame/game_home.html.twig');
    }

    #[Route("/game/blackjack", name: "game_twenty_one")]
    public function blackjack(SessionInterface $session): Response
    {
        //$deck = new DeckOfCards();
        //$deck->shuffle();
        //$session->set("deck", $deck);
        return $this->render('cardGame/twenty_one.html.twig');
    }
}
