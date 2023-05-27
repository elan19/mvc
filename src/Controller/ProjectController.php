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
        $form = $this->createFormBuilder()
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

        $form->handleRequest($request);

        $session->set('setupInProgress', true);

        if ($form->isSubmitted() && $form->isValid()) {
            $numPlayers = $form->getData()['numPlayers'];
            // Redirect to the blackjack route with the number of players
            return $this->redirectToRoute('blackjack_setup', ['numPlayers' => $numPlayers]);
        }

        return $this->render('project/home.html.twig', [
            'form' => $form->createView(),
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

        // Check if the form is submitted and the number of players is valid
        if ($request->isMethod('POST') && $numPlayers >= 1 && $numPlayers <= 3) {
            // Store the number of players in the session
            $session->set('numPlayers', $numPlayers);

            // Store the player names in an array
            $playerNames = [];
            for ($i = 1; $i <= $numPlayers; $i++) {
                $playerName = $request->request->get('playerName' . $i);
                $playerNames[$i] = $playerName;
            }
            $session->set('playerNames', $playerNames);

            // Redirect to the blackjack route
            return $this->redirectToRoute('blackjack', ['numPlayers' => $numPlayers]);
        }

        return $this->render('project/setup.html.twig', [
            'numPlayers' => $numPlayers,
            'game' => $session->get('setupInProgress'),
        ]);
    }

    #[Route("/proj/blackjack", name: "blackjack_solo")]
    public function blackjack_solo(): Response
    {
        return $this->redirectToRoute('blackjack', ['numPlayers' => 1]);
    }

    #[Route("/proj/blackjack/{numPlayers}", name: "blackjack")]
    public function blackjack(Request $request, int $numPlayers, SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $playerHands = $session->get('playerHands', []);
        $dealerHand = $session->get('dealerHand');
        $playerNames = $session->get('playerNames', []);
        if ($session->get('setupInProgress')) {
            $session->set('setupInProgress', false);
        }

        // Initialize the game if the session data is not available
        if (!$deck || !$playerHands || !$dealerHand) {
            $deck = new DeckOfCards();
            $deck->shuffle();

            $playerHands = [];
            for ($i = 0; $i < $numPlayers; $i++) {
                $playerHand = new CardHand();
                $playerHand->addCard($deck->drawCard());
                $playerHand->addCard($deck->drawCard());
                $playerHands[] = $playerHand;
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