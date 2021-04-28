<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Entity\User;
use App\form\registerForm;
use App\Service\DatabaseService;
use App\Service\FunctionsService;
use App\Service\CheckUserService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use http\Message\Body;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DefaultController extends AbstractController
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
     * @var CheckUserService
     */
    private $checkUser;

    /**
     * DefaultController constructor.
     * @param Environment $twig
     * @param EntityManagerInterface $entityManager
     * @param DatabaseService $dbService
     * @param FunctionsService $functionsService
     * @param SessionInterface $sessionInterface
     * @param LoggerInterface $logger
     * @param CheckUserService $checkUser
     */
    public function __construct(
        Environment $twig,
        EntityManagerInterface $entityManager,
        DatabaseService $dbService,
        FunctionsService $functionsService,
        SessionInterface $sessionInterface,
        LoggerInterface $logger,
        CheckUserService $checkUser
    )
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->dbService = $dbService;
        $this->functionsService = $functionsService;
        $this->sessionInterface = $sessionInterface;
        $this->logger = $logger;
        $this->checkUser = $checkUser;
    }
    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @Route ("/", name="/")
     */
    public function index(){

        $matchList = json_decode(file_get_contents("../API/APIDataConverted.json"), true);
        $test =  $this->sessionInterface->get('user_id');

        $user = null;
        if (!empty($test)){
            $user = $this->dbService->getUserById($test);
            $user = array(
                "pseudo" => $user->getPseudo(),
                "credits" => $user->getCredits()
            );
        }

        $display = $this->twig->render('/base.html.twig', [
            'connected' => $user,
            'matchList' => $matchList["data"],
        ]);
        return new Response($display);
    }


    /**
     * @param Request $request
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Exception
     * @Route ("/signIn", name="signIn")
     */
    public function signIn(Request $request){

        $date = new DateTime();

        // This argument gives the current date and is used in the twig,
        // so that the user cannot enter a birth date higher than now.
        $maxBirthDate = date_format($date, 'Y-m-d');

        $checkPwd = $this->checkUser->check_password($request->get("password"));
        $checkConfPwd = $this->checkUser->checkCorrespondanceBetween2Passwords(
            $request->get("password"),
            $request->get("confirm_password")
        );

        if (gettype($checkPwd) === 'string'){
            $display = $this->twig->render('/Home/login.html.twig', [
                'error' => $checkPwd,
                'signIn' => true,
                'dateNow' => $maxBirthDate
            ]);
            return new Response($display);
        }
        if ($checkConfPwd == false) {
            $display = $this->twig->render('/Home/login.html.twig', [
                'error' => 'Your confirm password is different',
                'signIn' => true,
                'dateNow' => $maxBirthDate
            ]);
            return new Response($display);
        }

        $usernameExists = $this->dbService->getByPseudo($request->get("username"));
        $mailExists = $this->dbService->getByEmail($request->get("email"));

        $userBirthDate = new DateTime($request->get("birthdate"));

        $pwd_hash = password_hash($request->get("password"), PASSWORD_DEFAULT);

        $user = new User(
            $request->get("username"),
            $request->get("name"),
            $pwd_hash,
            $userBirthDate,
            $request->get("email")
        );

        $dateNow = new DateTime();

        $userAge = $dateNow->diff($userBirthDate);

        /* check if the user is > 18 years old  */

        if ($userAge->y < 18){
            $display = $this->twig->render('/Home/login.html.twig', [
                'error' => 'You must be of legal age to enjoy BETFLIX',
                'signIn' => true,
                'dateNow' => $maxBirthDate
            ]);
        }
        else {
            if (empty($usernameExists) && empty($mailExists)){

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('logInPage');
            }
            else {

                if (!empty($usernameExists)){
                    $display = $this->twig->render('/Home/login.html.twig', [
                        'error' => 'A user with this pseudo ('.$request->get("username").') already exists',
                        'signIn' => true,
                        'dateNow' => $maxBirthDate
                    ]);
                }
                if (!empty($mailExists)){
                    $display = $this->twig->render('/Home/login.html.twig', [
                        'error' => 'A user with this email ('.$request->get("email").') already exists',
                        'signIn' => true,
                        'dateNow' => $maxBirthDate
                    ]);
                }
            }
        }
        return new Response($display);

    }

    /**
     * @param Request $request
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @Route ("/logIn", name="logIn")
     */
    public function logIn(Request $request){
        /**
         * @var User $userRegistered
         */
        $userRegistered = $this->dbService->getByEmail($request->get('email'));

        if (empty($userRegistered)){
            $display = $this->twig->render('/Home/login.html.twig', [
                'error' => 'No account found with this email',
                'logIn' => true
            ]);
        }
        else {
            if (password_verify($request->get('password'), $userRegistered->getPassword())){

                $this->functionsService->setSession($userRegistered->getId(), $userRegistered->getName());

                return $this->redirectToRoute('/');
            }
            else {
                $display = $this->twig->render('/Home/login.html.twig', [
                    'error' => 'The combination of email & password is not correct',
                    'logIn' => true
                ]);
            }
        }
        return new Response($display);
    }


    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @Route ("/signInPage", name="signInPage")
     */
    public function signInPage(){
        $date = new DateTime();
        $aa = date_format($date, 'Y-m-d');
        $display = $this->twig->render('/Home/login.html.twig', [
            'signIn' => true,
            'dateNow' => $aa
        ]);
        return new Response($display);
    }

    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @Route ("/logInPage", name="logInPage")
     */
    public function logInPage(){
        $display = $this->twig->render('/Home/login.html.twig', [
            'logIn' => true
        ]);
        return new Response($display);
    }

    /**
     * @return RedirectResponse
     * @Route ("/logOut", name="logOut")
     */
    public function logOut(){
        $this->sessionInterface->clear();
        return $this->redirectToRoute('/');
    }

}
