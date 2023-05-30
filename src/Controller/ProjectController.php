<?php

namespace App\Controller;

use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\DeckOfCards;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjectController extends AbstractController
{
    #[Route("/proj", name: "proj_home")]
    public function home(SessionInterface $session): Response
    {
        if ($session->has('gameInProgress') === false) {
            $session->set('gameInProgress', false);
        }

        if ($session->has('betInProgress') === false) {
            $session->set('betInProgress', false);
        }

        return $this->render('project/home.html.twig', [
            'game' => $session->get('gameInProgress'),
            'bet' => $session->get('betInProgress'),
        ]);
    }


    #[Route("/proj/about", name: "about")]
    public function about(): Response
    {
        return $this->render('project/about.html.twig');
    }

    #[Route("/proj/blackjack/setup", name: "blackjack_setup")]
    public function blackjackSetup(Request $request, SessionInterface $session): Response
    {
        $numPlayers = $request->request->get('numPlayers');

        if ($request->isMethod('POST') && $numPlayers >= 1 && $numPlayers <= 3) {
            $session->set('numPlayers', $numPlayers);
        }

        if(!$session->has('numPlayers')) {
            return $this->redirectToRoute('proj_home');
        }

        return $this->render('project/setup.html.twig', [
            'numPlayers' => $numPlayers,
            'game' => $session->get('gameInProgress'),
        ]);
    }

    #[Route("/proj/blackjack/bet", name: "blackjack_bet")]
    public function blackjackBet(Request $request, SessionInterface $session): Response
    {
        $numPlayers = $session->get('numPlayers');
        $playerNames = [];

        if ($request->isMethod('POST') && $request->request->has('playerName1')) {
            for ($i = 1; $i <= $numPlayers; $i++) {
                $playerName = $request->request->get("playerName$i");
                $playerNames[$i-1] = $playerName;
            }
            $session->set('playerNames', $playerNames);
        }

        if (errorChecksBet($session) != "") {
            $returnRoute = errorChecksBet($session);
            return $this->redirectToRoute($returnRoute);
        }

        $playerHands = $session->get('playerHands', []);

        if (is_array($playerHands)) {
            foreach ($playerHands as $playerHand) {
                $playerHand->resetHand();
            }
        }

        return $this->render('project/bet.html.twig', [
            'numPlayers' => $numPlayers,
            'playerHands' => $playerHands,
            'playerNames' => $session->get('playerNames', []),
            'bet' => $session->get('betInProgress'),
        ]);
    }

    #[Route("/proj/blackjack", name: "blackjack_solo")]
    public function blackjack_solo(): Response
    {
        return $this->redirectToRoute('blackjack_game');
    }

    #[Route("/proj/blackjack/game", name: "blackjack_game")]
    public function blackjack(Request $request, SessionInterface $session): Response
    {
        $numPlayers = $session->get('numPlayers');

        $deck = $session->get('deck');
        $playerHands = $session->get('playerHands', []);

        if ($request->isMethod('POST') && $request->request->has('betAmount1') && is_array($playerHands)) {
            $playerBets = [];
            for ($i = 1; $i <= $numPlayers; $i++) {
                $playerBet = $request->request->get("betAmount$i");
                $playerBets[$i] = $playerBet;
            }
            $session->set('playerBets', $playerBets);
        }
        $playerBets = $session->get('playerBets', []);

        if (errorChecksGame($session) != "") {
            $returnRoute = errorChecksGame($session);
            return $this->redirectToRoute($returnRoute);
        }

        if ($session->get('gameInProgress') == false) {
            $session->set('gameInProgress', true);
            $session->set('betInProgress', false);
        }

        $playerNames = $session->get('playerNames', []);
        $dealerHand = $session->get('dealerHand');

        if (!$deck || !$playerHands || !$dealerHand) {
            $session = createNewGame($session);
        }

        // Process player actions
        checkPlayerAction($request, $session);

        $deck = $session->get('deck');

        if (checkIfStopped($session)) {
            return $this->redirectToRoute('blackjack_winner');
        }

        return $this->render('project/blackjack.html.twig', [
            'numPlayers' => $numPlayers,
            'dealerHand' => $session->get('dealerHand'),
            'playerHands' => $playerHands,
            'playerNames' => $playerNames,
            'session' => $session,
        ]);
    }

    #[Route("/proj/blackjack/result", name: "blackjack_winner")]
    public function blackjackWinner(SessionInterface $session): Response
    {
        $playerHands = $session->get('playerHands', []);
        $dealerHand = $session->get('dealerHand');
        $playerNames = $session->get('playerNames', []);

        $winners = [];
        $losers = [];

        if (is_iterable($playerHands) && is_iterable($playerNames) && is_array($playerHands) && is_array($playerNames) && 
        $dealerHand instanceof CardHand) {
            for ($i = 0; $i < $session->get('numPlayers'); $i++) {
                $bet = $playerHands[$i]->getBet();

                if (dealerWon($session, $playerHands[$i], $dealerHand) === true) {
                    $losers[] = ['name' => $playerNames[$i], 'bet' => $bet];
                } elseif (playerWon($session, $playerHands[$i], $dealerHand) === true) {
                    $winners[] = ['name' => $playerNames[$i], 'bet' => $bet];
                    $wonMoney = $bet * 1.5;
                    $playerHands[$i]->updateTotalMoney($wonMoney);
                } elseif ($playerHands[$i]->getHandValue() == $dealerHand->getHandValue()) {
                    $playerHands[$i]->updateTotalMoney($bet);
                }
            }
        }

        if ($session->get('gameInProgress') == true) {
            $session->set('gameInProgress', false);
            $session->set('betInProgress', true);
        }

        $session->remove('deck');
        $session->remove('currentPlayer');
        $session->remove('playerBets');

        return $this->render('project/result.html.twig', [
            'winners' => $winners,
            'losers' => $losers,
            'dealerHand' => $dealerHand,
            'playerHands' => $playerHands,
            'playerNames' => $playerNames,
        ]);
    }

    #[Route("/proj/reset", name: "reset")]
    public function reset(SessionInterface $session): Response
    {
        $session->clear();
        return $this->redirectToRoute('proj_home');
    }

    #[Route("/proj/add/{playerIndex}", name: "add_money")]
    public function addMoney(int $playerIndex, SessionInterface $session): Response
    {
        $playerHands = $session->get('playerHands', []);

        if(is_array($playerHands)) {
            $playerHand = $playerHands[$playerIndex];
            if($playerHand instanceof CardHand) {
                $playerHand->updateTotalMoney(25);
            }
        }

        return $this->redirectToRoute('blackjack_bet');
    }
}

function createNewGame(SessionInterface $session): SessionInterface
{
    $deck = new DeckOfCards();
    $deck->shuffle();
    $playerHands = $session->get('playerHands', []);
    $playerBets = $session->get('playerBets', []);

    if (is_array($playerHands) && is_array($playerBets)) {
        for ($i = 0; $i < $session->get('numPlayers'); $i++) {
            $playerHands[$i]->addCard($deck->drawCard());
            $playerHands[$i]->addCard($deck->drawCard());
            $playerHands[$i]->setBet($playerBets[$i+1]);
            $playerHands[$i]->updateTotalMoney(-$playerBets[$i+1]);
        }
    }

    $dealerHand = new CardHand();
    $dealerHand->addCard($deck->drawCard());

    // Save the initial game state to the session
    $session->set('deck', $deck);
    $session->set('playerHands', $playerHands);
    $session->set('dealerHand', $dealerHand);
    $session->set('currentPlayer', 1); // Set the first player as the current player
    return $session;
}

function checkPlayerAction(Request $request, SessionInterface $session): SessionInterface
{
    $formData = $request->request->all();
    $playerHands = $session->get('playerHands', []);
    $deck = $session->get('deck');
    $numPlayers = $session->get('numPlayers');
    
    if (isset($formData['playerIndex']) && isset($formData['action'])) {
        $playerIndex = $formData['playerIndex'] - 1;
        $action = $formData['action'];

        if (is_array($playerHands) && isset($playerHands[(int)$playerIndex]) && $playerIndex === $session->get('currentPlayer') - 1) {
            $playerHand = $playerHands[(int)$playerIndex];
            if ($action === 'hit' && $playerHand instanceof CardHand) {
                if ($deck instanceof DeckOfCards) {
                    $playerHand->addCard($deck->drawCard());
                }
                if ($playerHand->isBust()) {
                    // Move to the next player
                    $playerHand->stand();
                    $currentPlayer = $session->get('currentPlayer');
                    $currentPlayer++;
                    if ($currentPlayer > $numPlayers) {
                        $currentPlayer = 1; // Start over from the first player
                    }
                    $session->set('currentPlayer', $currentPlayer);
                }
            }
            if ($action === 'stand' && $playerHand instanceof CardHand) {
                $playerHand->stand();

                // Move to the next player
                $currentPlayer = $session->get('currentPlayer');
                $currentPlayer++;
                if ($currentPlayer > $numPlayers) {
                    $currentPlayer = 1; // Start over from the first player
                }
                $session->set('currentPlayer', $currentPlayer);
            }

            $session->set('playerHands', $playerHands);
        }
    }
    return $session;
}

function checkIfStopped(SessionInterface $session): bool
{
    $playerHands = $session->get('playerHands', []);
    $dealerHand = $session->get('dealerHand');
    $deck = $session->get('deck');
    if (is_iterable($playerHands) && $dealerHand instanceof CardHand) {
        $allPlayersStood = true;
        foreach ($playerHands as $playerHand) {
            if ($playerHand instanceof CardHand && !$playerHand->isStand()) {
                $allPlayersStood = false;
                break;
            }
        }
        if ($allPlayersStood && !$dealerHand->isStand() && $deck instanceof DeckOfCards) {
            while ($dealerHand->getHandValue() < 17) {
                $dealerHand->addCard($deck->drawCard());
            }
            $dealerHand->stand();
            $session->set('dealerHand', $dealerHand);
            return true;
        }
    }
    return false;
}

function errorChecksGame(SessionInterface $session): string
{
    $playerBets = $session->get('playerBets', []);
    if(empty($session->get('numPlayers'))) {
        return 'proj_home';
    }

    if (!$session->has('betInProgress')) {
        return 'blackjack_setup';
    }

    if (empty($playerBets)) {
        return 'blackjack_bet';
    }

    return '';
}

function errorChecksBet(SessionInterface $session): string
{
    if ($session->has('dealerHand')) {
        $session->remove('dealerHand');
    }

    if($session->get('numPlayers') == null) {
        return 'proj_home';
    }

    if (!$session->get('playerNames')) {
        return 'blackjack_setup';
    }

    if (!$session->has('playerHands')) {
        $playerHands = [];
        for ($i = 0; $i < $session->get('numPlayers'); $i++) {
            $playerHands[$i] = new CardHand();
        }
        $session->set('playerHands', $playerHands);
    }

    if (!$session->get('betInProgress')) {
        $session->set('betInProgress', true);
    }

    if ($session->get('gameInProgress')) {
        return 'blackjack_game';
    }

    return '';
}

function dealerWon(SessionInterface $session, CardHand $playerHand, CardHand $dealerHand): bool
{
    $playerHandValue = $playerHand->getHandValue();
    $dealerHandValue = $dealerHand->getHandValue();
    if ($playerHand->isBust() || ($dealerHandValue <= 21 && 
        $playerHandValue < $dealerHandValue) || $playerHandValue > 21) {
        return true;
    }
    return false;
}

function playerWon(SessionInterface $session, CardHand $playerHand, CardHand $dealerHand): bool
{
    $playerHandValue = $playerHand->getHandValue();
    $dealerHandValue = $dealerHand->getHandValue();
    if (($playerHandValue <= 21 && $dealerHandValue < $playerHandValue) || $dealerHandValue > 21)
    {
        return true;
    }
    return false;
}
