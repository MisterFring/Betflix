<?php

require "../src/Service/FunctionsService.php";

clearstatcache();

$curl = curl_init();
$DATA = "APIData.json";

if(filesize($DATA)) {
    file_put_contents($DATA, "");
}

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api-football-v1.p.rapidapi.com/v2/fixtures/league/2664/next/10?timezone=Europe%2FLondon",
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

file_put_contents('APIData.json', $response);


$data = json_decode(file_get_contents("APIData.json"), true);
$matchList = $data["api"]["fixtures"];
$returnArray = array();

foreach ($matchList as $match) {
    $oddsArray = getOdds($match["fixture_id"]);
    $myMatch = array(
        "id" => $match["fixture_id"],
        "leagueId" => $match["league_id"],
        "leagueName" => $match["league"]["name"],
        "homeTeam" => $match["homeTeam"]["team_name"],
        "awayTeam" => $match["awayTeam"]["team_name"],
        "date" => $match['event_date'],
        "homeOdd" => $oddsArray['homeOdd'],
        "drawOdd" => $oddsArray['drawOdd'],
        "awayOdd" => $oddsArray['awayOdd']
    );
    array_push($returnArray, $myMatch);
}
$json = json_encode(array('data' => $returnArray));
file_put_contents('APIDataConverted.json', $json);


function getOdds($id){
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api-football-v1.p.rapidapi.com/v2/odds/fixture/" . $id . "/bookmaker/6",
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
    //file_put_contents('APIOdds.json', $response);

    $data = json_decode($response, true);
    //[0]["bookmakers"]["bets"][0]['values'];

    $dataOdds = $data["api"]["odds"][0]["bookmakers"][0]["bets"][0]['values'];

    /*$json = json_encode(array('data' => $dataOdds));

    file_put_contents('APIOdds.json', $json);*/


    return array(
        "homeOdd" => floatval($dataOdds[0]['odd']),
        "drawOdd" => floatval($dataOdds[1]['odd']),
        "awayOdd" => floatval($dataOdds[2]['odd'])
    );

}
