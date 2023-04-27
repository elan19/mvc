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

        if ($session->has("deck") == false) {
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
        if ($session->has("deck") == false) {
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
            if ($deck->cardsLeft() > 0) {
                $card = $session->get("lastCard");
            }
            return $this->render('cardGame/draw.html.twig', ['deck' => $deck, 'card' => $card,]);
        }
        return $this->render('cardGame/draw.html.twig', ['deck' => $deck,]);
    }

    #[Route("/card/deck/draw/{num<\d+>}", name: "draw_cards")]
    public function drawCards(SessionInterface $session, int $num): Response
    {
        if ($session->has("deck") == false) {
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
        return $this->render('cardGame/game_home.html.twig');
    }

    #[Route("/game/blackjack", name: "game_twenty_one")]
    public function blackjack(SessionInterface $session): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $session->set("deck", $deck);
        $deck = $session->get("deck");
        $playerHand = new CardHand();
        $dealerHand = new CardHand();
        if ($deck instanceof DeckOfCards) {
            $card = $deck->drawCard();
            $playerHand->addCard($card);
            $session->set("playerhand", $playerHand);
            $session->set("dealerhand", $dealerHand);
            $session->set("stay", false);
            return $this->render('cardGame/twenty_one.html.twig', ['player' => $playerHand, 'dealer' => $dealerHand]);
        }
        return $this->render('cardGame/twenty_one.html.twig');
    }

    #[Route("/game/blackjack/draw", name: "game_twenty_one_draw")]
    public function blackjackDraw(SessionInterface $session): Response
    {
        if (!$session->has("deck")) {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $session->set("deck", $deck);
        }

        $deck = $session->get("deck");
        $playerHand = $session->get("playerhand");
        $dealerHand = $session->get("dealerhand");
        $didStay = $session->get("stay");

        if (!$playerHand instanceof CardHand || !$dealerHand instanceof CardHand) {
            return $this->render('cardGame/twenty_one.html.twig');
        }
        if ($playerHand->getHandValue() >= 21 || $didStay) {
            while ($dealerHand->getHandValue() < 17 && $deck instanceof DeckOfCards) {
                $card = $deck->drawCard();
                $dealerHand->addCard($card);
                $session->set("dealerhand", $dealerHand);
            }
            return $this->render('cardGame/twenty_one.html.twig', ['player' => $playerHand, 'dealer' => $dealerHand]);
        }

        if ($deck instanceof DeckOfCards) {
            $card = $deck->drawCard();
            $playerHand->addCard($card);
            $session->set("playerhand", $playerHand);
            $session->set("dealerhand", $dealerHand);
        }

        return $this->render('cardGame/twenty_one.html.twig', ['player' => $playerHand, 'dealer' => $dealerHand]);
    }

    #[Route("/game/blackjack/stay", name: "game_twenty_one_stay")]
    public function blackjackStay(SessionInterface $session): Response
    {
        if ($session->has("deck") == false) {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $session->set("deck", $deck);
        }
        $deck = $session->get("deck");
        $session->set("stay", true);
        if ($session->has("playerhand") && $session->has("dealerhand")) {
            $playerHand = $session->get("playerhand");
            $dealerHand = $session->get("dealerhand");
            if ($dealerHand instanceof CardHand) {
                while ($dealerHand->getHandValue() < 17) {
                    if ($deck instanceof DeckOfCards) {
                        $card = $deck->drawCard();
                        $dealerHand->addCard($card);
                        $session->set("dealerhand", $dealerHand);
                    }
                }
            }
            return $this->render('cardGame/twenty_one.html.twig', ['player' => $playerHand, 'dealer' => $dealerHand]);
        }
        return $this->render('cardGame/twenty_one.html.twig');
    }

    #[Route("/game/doc", name: "game_doc")]
    public function gameDoc(): Response
    {
        return $this->render('cardGame/game_dock.html.twig');
    }
}
