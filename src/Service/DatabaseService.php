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

        $betsToRandom = array();
        $dateNow = new \DateTime();

        // pas réussi à trouver comment utiliser un where pour directement conditionner
        // ma requete sql et éviter d'importer tous les paris
        $bets = $this->getDataBet()->findAll();

        foreach ($bets as $bet){
            $diff = $dateNow->getTimestamp() - $bet->getBetDateEnd()->getTimestamp();
            if ($diff > 0 && $bet->getWin() === 'in progress') {
                array_push($betsToRandom, $bet);
            }
        }
// sans le true, on se retrouve avec des objets de class stdClass
        //$json = json_decode($ApiDataJSON, true);
        //$this->getResultFromMatchId(157508);
        $data = json_decode(file_get_contents("/Users/Pierredck/Documents/Coding Factory/Betflix/API/test.json"), true);
        $score = $data['api']['fixtures'][0]['score']['fulltime'];

        $out = [];
        preg_match_all('/\d+/', $score, $out, PREG_PATTERN_ORDER);
        $goalNumberHome = $out[0][0];
        $goalNumberAway = $out[0][1];
        $diff = $goalNumberHome - $goalNumberAway;
        if ($diff == 0){
            $matchResult = 'N';
        }
        else{
            if ($diff > 0){
                $matchResult = '1';
            } else {
                $matchResult = '2';
            }
        }
        print $matchResult;
        foreach ($betsToRandom as $bet){
            //print_r($bet->getData());
            //faire avec id en dur


            /* $rand = rand(0,1);
            $bool = ($rand === 0) ? 'win' : 'loss';

            // Add the gain if bet wins
            if ($bool === 'win'){
                $user = $bet->getUser();
                $gain = $bet->getOddsTotal() * $bet->getBetAmount();
                /**
                 * @var User $user

                $user->setCredits(($user->getCredits()) + $gain);
            }

            $bet->setWin($bool);*/
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
            echo $response;
        }

        file_put_contents('/Users/Pierredck/Documents/Coding Factory/Betflix/API/test.json', $response);
    }

}