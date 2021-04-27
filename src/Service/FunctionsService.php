<?php


namespace App\Service;
use App\Service\DatabaseService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



class FunctionsService
{
    /**
     * @var SessionInterface
     */
    private $sessionInterface;

    /**
     * @var DatabaseService
     */
    private $dbService;

    /**
     * FunctionsService constructor.
     * @param SessionInterface $sessionInterface
     * @param \App\Service\DatabaseService $dbService
     */
    public function __construct(SessionInterface $sessionInterface, DatabaseService $dbService)
    {
        $this->sessionInterface = $sessionInterface;
        $this->dbService = $dbService;
    }

    public function setSession($id, $name) {
        $this->sessionInterface->set('user_id', $id);
        $this->sessionInterface->set('name', $name);
    }

    public function verifyBet($betsArray){

        $json = file_get_contents("../API/APIDataConverted.json");

        $newJson = json_decode($json, true);//newJson type = array

        //[{"match":"OL-OM","choice":"home","odd":"2.3"},{"match":"PSG-MU","choice":"home","odd":"1.9"}]

        $returnArray = array();
        foreach($betsArray as $betUser){
            switch ($betUser['choice']){
                case 'home' :
                    $index = "homeOdd";
                    break;
                case 'draw' :
                    $index = "drawOdd";
                    break;
                case 'away' :
                    $index = "awayOdd";
                    break;
                default :
                    $index = 'User has modified data';
            }
            foreach ($newJson['data'] as $matchData){
                if ($betUser['id'] == $matchData['id']){

                    $date = new \DateTime($matchData['date']);
                    $dateTimestamp = $date->getTimestamp();

                    $myObj = array(
                        "match" => $matchData['homeTeam']."-".$matchData['awayTeam'],
                        "choice" => $betUser['choice'],
                        "odd" => $matchData[$index],
                        "dateTimestamp" => $dateTimestamp,
                        "idApi" => $matchData['id']
                    );
                    array_push($returnArray, $myObj);
                }

            }
        }
        $oddsTotal = 1;
        foreach ($returnArray as $item){
            $oddsTotal *= $item['odd'];
        }

        $return = array(
            "bets" => $returnArray,
            "oddsTotal" => $oddsTotal
        );
        if (count($betsArray) === count($returnArray)){
            return $return;
        }
        else {
            return false;
        }

    }

    public function getLatestMatchDate($matchList){
        $latest = 0;
        foreach ($matchList as $match){
            if ($match['dateTimestamp'] > $latest){
                $latest = $match['dateTimestamp'];
            }
        }
        return $latest;
    }

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }


}