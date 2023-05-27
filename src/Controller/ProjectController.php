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
    public function home(Request $request, SessionInterface $session): Response
    {
        /*$form = $this->createFormBuilder()
            ->add('numPlayers', IntegerType::class, [
                'label' => 'Number of Players:',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(1),
                    new LessThanOrEqual(3),
                ],
            ])
            ->add('startGame', SubmitType::class, ['label' => 'Start Game'])
            ->getForm();

        $form->handleRequest($request);*/
        if ($session->has('gameInProgress') == false) {
            $session->set('gameInProgress', false);
        }

        /*if ($form->isSubmitted() && $form->isValid()) {
            $numPlayers = $form->getData()['numPlayers'];
            var_dump("hej");
            // Redirect to the blackjack route with the number of players
            //return $this->redirectToRoute('blackjack_setup', ['numPlayers' => $numPlayers]);
        }*/

        return $this->render('project/home.html.twig', ['game' => $session->get('gameInProgress')]);
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

        if($numPlayers == null) {
            return $this->redirectToRoute('proj_home');
        }

        // Check if the form is submitted and the number of players is valid
        if ($request->isMethod('POST') && $numPlayers >= 1 && $numPlayers <= 3) {
            // Store the number of players in the session
            $session->set('numPlayers', $numPlayers);
        }
            // Redirect to the blackjack route
            //return $this->redirectToRoute('blackjack', ['numPlayers' => (int)$numPlayers]);

        return $this->render('project/setup.html.twig', [
            'numPlayers' => $numPlayers,
            'game' => $session->get('gameInProgress'),
        ]);
    }

    #[Route("/proj/blackjack/bet", name: "blackjack_bet")]
    public function blackjackBet(Request $request, SessionInterface $session): Response
    {
        $numPlayers = $session->get('numPlayers');

        if($numPlayers == null) {
            return $this->redirectToRoute('proj_home');
        }

        // Check if the form is submitted and the number of players is valid
        /*if ($request->isMethod('POST') && $numPlayers >= 1 && $numPlayers <= 3) {
            // Store the number of players in the session
            $session->set('numPlayers', $numPlayers);
        }*/
        var_dump($numPlayers);

        if ($request->isMethod('POST') && $request->request->has('playerName1')) {
            $playerNames = [];
            for ($i = 1; $i <= $numPlayers; $i++) {
                $playerName = $request->request->get("playerName$i");
                $playerNames[$i] = $playerName;
            }
            $session->set('playerNames', $playerNames);
        }

        if (!$session->has('playerHands')) {
            $playerHands = [];
            for ($i = 0; $i < $numPlayers; $i++) {
                $playerHands[$i] = new CardHand();
            }
            $session->set('playerHands', $playerHands);
        }
        $playerHands = $session->get('playerHands', []);
        $playerNames = $session->get('playerNames', []);
        var_dump($playerHands);
        var_dump($playerNames);

        if (!$session->has('betInProgress')) {
            $session->set('betInProgress', true);
        }

        return $this->render('project/bet.html.twig', [
            'numPlayers' => $numPlayers,
            'playerHands' => $playerHands,
            'playerNames' => $playerNames,
            'game' => $session->get('gameInProgress'),
            'bet' => $session->get('betInProgress'),
        ]);
    }

    #[Route("/proj/blackjack", name: "blackjack_solo")]
    public function blackjack_solo(): Response
    {
        return $this->redirectToRoute('blackjack/game');
    }

    #[Route("/proj/blackjack/game", name: "blackjack_game")]
    public function blackjack(Request $request, SessionInterface $session): Response
    {
        $numPlayers = $session->get('numPlayers');

        if(empty($numPlayers)) {
            return $this->redirectToRoute('proj_home');
        }

        $deck = $session->get('deck');
        $playerHands = $session->get('playerHands', []);

        /*if(!$playerHands) {
            return $this->redirectToRoute('blackjack_setup');
        }*/

        if ($request->isMethod('POST') && $request->request->has('betAmount1')) {
            $playerBets = [];
            for ($i = 1; $i <= $numPlayers; $i++) {
                $playerBet = $request->request->get("betAmount$i");
                $playerBets[$i] = $playerBet;
                if (($playerHands[$i-1]->getTotalMoney() - $playerBet) < 0) {
                    return $this->redirectToRoute('blackjack_bet');
                }
            }
            $session->set('playerBets', $playerBets);
        }
        $playerNames = $session->get('playerNames', []);
        $playerBets = $session->get('playerBets', []);

        var_dump($playerNames);
        var_dump($playerBets);
        var_dump($playerHands);

        $dealerHand = $session->get('dealerHand');

        if ($session->get('gameInProgress') == false) {
            $session->set('gameInProgress', true);
            $session->set('betInProgress', false);
        }

        // Initialize the game if the session data is not available
        if (!$deck || !$playerHands || !$dealerHand) {
            $deck = new DeckOfCards();
            $deck->shuffle();

            for ($i = 0; $i < $numPlayers; $i++) {
                $playerHands[$i]->addCard($deck->drawCard());
                $playerHands[$i]->setBet($playerBets[$i+1]);
                $playerHands[$i]->updateTotalMoney(-$playerBets[$i+1]);
            }

            for ($i = 0; $i < $numPlayers; $i++) {
                $playerHands[$i]->addCard($deck->drawCard());
            }

            $dealerHand = new CardHand();
            $dealerHand->addCard($deck->drawCard());

            // Save the initial game state to the session
            $session->set('deck', $deck);
            $session->set('playerHands', $playerHands);
            $session->set('dealerHand', $dealerHand);
            $session->set('currentPlayer', 1); // Set the first player as the current player
        }

        // Process player actions
        if ($request->isMethod('POST')) {
            $formData = $request->request->all();
            if (isset($formData['playerIndex']) && isset($formData['action'])) {
                $playerIndex = $formData['playerIndex'] - 1;
                $action = $formData['action'];

                if (isset($playerHands[$playerIndex]) && $playerIndex === $session->get('currentPlayer') - 1) {
                    $playerHand = $playerHands[$playerIndex];

                    if ($action === 'hit') {
                        $playerHand->addCard($deck->drawCard());
                        if ($playerHand->isBust()) {
                            // Move to the next player
                            $currentPlayer = $session->get('currentPlayer');
                            $currentPlayer++;
                            if ($currentPlayer > $numPlayers) {
                                $currentPlayer = 1; // Start over from the first player
                            }
                            $session->set('currentPlayer', $currentPlayer);
                        }
                    } elseif ($action === 'stand') {
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
        }

        // Process dealer's turn if all players have stood
        $allPlayersStood = true;
        foreach ($playerHands as $playerHand) {
            if (!$playerHand->isStand()) {
                $allPlayersStood = false;
                break;
            }
        }

        if ($allPlayersStood && !$dealerHand->isStand()) {
            while ($dealerHand->getHandValue() < 17) {
                $dealerHand->addCard($deck->drawCard());
            }
            $dealerHand->stand();
            $session->set('dealerHand', $dealerHand);
        }

        return $this->render('project/blackjack.html.twig', [
            'numPlayers' => $numPlayers,
            'dealerHand' => $dealerHand,
            'playerHands' => $playerHands,
            'playerNames' => $playerNames,
            'session' => $session,
        ]);
    }
}