<?php

namespace App\Service;

use App\Entity\Bet;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DatabaseService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * DatabaseService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

                /*    Database functions for User Entity    */

    /**
     * @return mixed
     */
    public function getDataUser(){
        return $this->entityManager->getRepository(User::class);
    }

    public function getAll(){
        return $this->getDataUser()->findAll();
    }

    /**
     * @param $id
     * @return User
     */
    public function getUserById($id){
        return $this->getDataUser()->findOneBy(['id' => $id]);
    }

    /**
     * @param $pseudo
     * @return User
     */
    public function getByPseudo($pseudo){
        return $this->getDataUser()->findOneBy(['pseudo' => $pseudo]);
    }

    /**
     * @param $mail
     * @return User
     */
    public function getByEmail($mail){
        return $this->getDataUser()->findOneBy(['mail' => $mail]);
    }

                /*    Database functions for Bet Entity     */

    /**
     * @return mixed
     */
    public function getDataBet(){
        return $this->entityManager->getRepository(Bet::class);
    }

    /**
     * @param $id
     * @return Bet
     */
    public function getBetById($id){
        return $this->getDataBet()->findOneBy(['id' => $id]);
    }

    public function setResults(){
        /**
         * @var Bet $bet
         */

        $betsToCheck = array();
        $dateNow = new \DateTime();

        // pas réussi à trouver comment utiliser un where pour directement conditionner
        // ma requete sql et éviter d'importer tous les paris
        $bets = $this->getDataBet()->findAll();

        foreach ($bets as $bet){
            $diff = $dateNow->getTimestamp() - $bet->getBetDateEnd()->getTimestamp();
            if ($diff > 0 && $bet->getWin() === 'in progress') {
                array_push($betsToCheck, $bet);
            }
        }

        $isBetWin = false;
        foreach ($betsToCheck as $bet){

            $matchesFrom1Bet = json_decode(json_encode($bet->getData()), True);
            //var_dump($matchesFrom1Bet);
            foreach ($matchesFrom1Bet as $match){
                //var_dump($match["idApi"]);
                $matchResult = 'away';//$this->getResultFromMatchId($match["idApi"]);
                if ($match['choice'] == $matchResult){
                    $isBetWin = true;
                }
                else {
                    $isBetWin = false;
                    break;
                }
            }
            if ($isBetWin) {
                $user = $bet->getUser();
                $gain = round(($bet->getOddsTotal() * $bet->getBetAmount()), 2);
                /**
                 * @var User $user
                 */
                $user->setCredits(($user->getCredits()) + $gain);
                $bet->setWin('win');
                $this->entityManager->persist($user);
            } else {
                $bet->setWin('loss');
            }
            $this->entityManager->persist($bet);
            $this->entityManager->flush();
        }


        /*$dateNow = new \DateTime();
        $aa = strval($dateNow);

        $test = new ResultSetMappingBuilder();
        $test->addEntityResult('App\Entity\Bet', 'b');
        $test->addFieldResult('b', 'id', 'id');
        $test->addFieldResult('b', 'betDateEnd', 'betDateEnd');

        $sql = "SELECT b.id FROM bet b WHERE b.betDateEnd < ?";

        $query = $this->entityManager->createNativeQuery($sql, $test);
        $query->setParameter(1, $dateNow);

        return $query->getResult();*/

    }

    public function getResultFromMatchId($matchId){

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api-football-v1.p.rapidapi.com/v2/fixtures/id/".$matchId."?timezone=Europe%2FLondon",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "x-rapidapi-host: api-football-v1.p.rapidapi.com",
                "x-rapidapi-key: 3603ce1f3fmsh2dce3af5d369b82p1b6895jsne13c17e180a2"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            //echo $response;
        }

        $data = json_decode($response,true);
        $score = $data["api"]["fixtures"][0]["score"]["fulltime"];

        $out = [];
        preg_match_all('/\d+/', $score, $out, PREG_PATTERN_ORDER);
        $goalNumberHome = $out[0][0];
        $goalNumberAway = $out[0][1];
        $diff = $goalNumberHome - $goalNumberAway;
        if ($diff == 0){
            $matchResult = 'draw';
        }
        else{
            if ($diff > 0){
                $matchResult = 'home';
            } else {
                $matchResult = 'away';
            }
        }
        return $matchResult;
    }

}