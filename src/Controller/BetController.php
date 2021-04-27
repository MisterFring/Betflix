<?php


namespace App\Controller;


use App\Entity\Bet;
use App\Entity\User;
use App\Service\DatabaseService;
use App\Service\FunctionsService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class BetController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DatabaseService
     */
    private $dbService;

    /**
     * @var FunctionsService
     */
    private $functionsService;

    /**
     * @var SessionInterface
     */
    private $sessionInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DefaultController constructor.
     * @param Environment $twig
     * @param EntityManagerInterface $entityManager
     * @param DatabaseService $dbService
     * @param FunctionsService $functionsService
     * @param SessionInterface $sessionInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        Environment $twig,
        EntityManagerInterface $entityManager,
        DatabaseService $dbService,
        FunctionsService $functionsService,
        SessionInterface $sessionInterface,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->dbService = $dbService;
        $this->functionsService = $functionsService;
        $this->sessionInterface = $sessionInterface;
        $this->logger = $logger;
    }

    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @Route ("/myBets", name="myBets")
     */
    public function myBets() {

        // to remove from this route //
        $this->dbService->setResults();
        // ------------------------ //

        $userId = $this->sessionInterface->get('user_id');
        /**
         * @var User $user
         */
        $user = $this->dbService->getUserById($userId);

        $userArray = array(
            "pseudo" => $user->getPseudo(),
            "credits" => $user->getCredits()
        );

        // We reverse the table to have the decreasing dates
        $userBets = array_reverse($user->getBet());
        $arrayResult = array();

        /**
         * @var Bet $bet
         */
        foreach ($userBets as $bet){
            switch ($bet->getWin()){
                case "in progress":
                    $color = "#DEDEDE";
                    break;
                case "win":
                    $color = "#B3FF81";
                    break;
                case "loss":
                    $color = "#FF6B6B";
                    break;
                default :
                    'error';
            }

            $arrayTampo = array(
                "oddsTotal" => round($bet->getOddsTotal(), 2),
                "betAmount" => round($bet->getBetAmount(), 2),
                "betDate" => $bet->getBetDate()->format('Y-m-d H:i:s'),
                "details" => $bet->getData(),
                "color" => $color
            );
            array_push($arrayResult, $arrayTampo);
        }

        $display = $this->twig->render('/Home/bets.html.twig', [
            'bets' => $arrayResult,
            'connected' => $userArray
        ]);
        return new Response($display);
    }

    /**
     * @Route ("/checkBet", name="checkBet")
     * @param Request $request
     * @return Response
     */
    public function checkBet(Request $request){

        $userId = $this->sessionInterface->get('user_id');
        // We check if the user is logged in, if not, he cannot bet.
        if (empty($userId)){
            $response = new Response(json_encode(
                array(
                    "result" => "error",
                    "message" => 'You have to be connected to bet !'
                )
            ));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
            //return new Response('You have to be connected to bet !', Response::HTTP_OK, []);
        }

        $content = json_decode($request->getContent(), true);

        if ($content["amount"] <= 0){
            $response = new Response(json_encode(
                array(
                    "result" => "error",
                    "message" => 'An error occurred during the validation of the bet'
                )
            ));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $check = $this->functionsService->verifyBet($content["bets"]);

        if ($check == false){
            $response = new Response(json_encode(
                array(
                    "result" => "error",
                    "message" => 'An error occurred during the validation of the bet'
                )
            ));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else {
            $user = $this->dbService->getUserById($userId);

            $diff = $user->getCredits() - $content['amount'];

            $latestMatchDate = $this->functionsService->getLatestMatchDate($check['bets']);
            // $latestMatchDate is an integer
            if ($diff < 0){
                $response = new Response(json_encode(
                    array(
                        "result" => "error",
                        "message" => "T'as cru que t'allais nous avoir ?"
                    )
                ));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            // To know the possible time of verification of the bet,
            // I add 2 hours to the time of the beginning of the match.
            $latestMatchDate += 60 * 60 * 2;
            $dateEnd = new \DateTime();
            $dateEnd->setTimestamp($latestMatchDate);


            $bet = new Bet($check['oddsTotal'], round($content['amount'], 2), $check['bets'], $dateEnd, $user);

            $user->setCredits(($user->getCredits()) - ($content['amount']));

            $em = $this->getDoctrine()->getManager();
            $em->persist($bet);
            $em->flush();

            $response = new Response(json_encode(
                array(
                    "result" => "success",
                    "message" => 'Bet placed',
                    "credits" => $user->getCredits()
                )
            ));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

    }
}