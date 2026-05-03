<?php

namespace App\Controllers;

class LiveScore extends BaseController
{
    protected $_key;
    protected $_secret;
    public $connection = null;
    protected $_baseUrl = "https://livescore-api.com/api-client/";

    protected function _buildUrl($endpoint, $params) {
        $params['key'] = $_ENV['lskey'];
        $params['secret'] = $_ENV['lsscr'];
        return $this->_baseUrl . $endpoint . '?' . http_build_query($params);
    }

    public function getLivescores($params = []) {
        $url = $this->_buildUrl('scores/live.json', $params);
        $data = $this->_makeRequest($url);
        foreach ($data['match'] as &$mecz) {
            $parametry['id'] = $mecz['id'];
            $eventurl = $this->_buildUrl('scores/events.json', $parametry);
            $wydarzenia['wydarzenia'] = $this->_makeRequest($eventurl);
            $mecz += $wydarzenia;
        }
        return $data['match'];
    }

    public function getFixtures($params = []) {
        $url = $this->_buildUrl('fixtures/matches.json', $params);
        $data = $this->_makeRequest($url);
        return $data['fixtures'];
    }

    public function getEvents($params = []){
        $url = $this->_buildUrl('scores/events.json', $params);
        $data = $this->_makeRequest($url);
        return $data['event'];
    }

    public function getHTH($params = []){
        $url = $this->_buildUrl('teams/head2head.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }    

    protected function _makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['data'];
    }

    public function test() {  
        $data['title'] = "Tu się będzie dziać coś strasznego";

        return view('live/header', $data)
               . view('live/test', $data)
               . view('live/footer', $data);
    }

    public function preMecz($mecz, $druzyna1, $druzyna2) {  
        $cashedPreMecz = "PreMecz_" . $mecz;
        $parametry = [
            'team1_id' => $druzyna1,
            'team2_id' => $druzyna2,
        ];

        if (!$preMecz = cache($cashedPreMecz)) {
            $data['h2h'] = $this->getHTH($parametry);
            $preMecz = view('live/eksperyment', $data, ['cache' => 60, 'cache_name' => $cashedPreMecz]);
        }

        return $preMecz;
    }

    public function index() {
        $tylko_ms_parametry_dzisiejsze['competition_id'] = "362";
        $parametry_dzisiejsze['competition_id'] = "362,363,387,274,271,227,244,245,1,2,3,4,60,209,349,350,446,149,150,151,152,153,167,169,179,178,333,334,111,205";
        $parametry_dzisiejsze['date'] = "today";

        $data['title'] = "Wyniki najciekawszych meczów piłkarskich";

        echo view('live/header', $data);
        echo $this->naZywo();
        echo $this->zaplanowaneNaDzis();
        echo view('live/footer', $data);
        echo view('live/skrypty', $data);
    }

    public function naZywo() {
        $parametry_live['competition_id'] = "362,363,387,274,271,227,244,245,1,2,3,4,60,209,349,350,446,149,150,151,152,153,167,169,179,178,333,334,111,205";
        $cachedLive = "live_general";
        
        if (!$live = cache($cachedLive)) {
            $data['live'] = $this->getLivescores($parametry_live);
            $live = view('live/naZywo', $data, ['cache' => 60, 'cache_name' => $cachedLive]);
        }   

        return $live;
    }

    public function zaplanowaneNaDzis() {
        $parametry_dzisiejsze['competition_id'] = "363,387,274,271,227,244,245,1,2,3,4,60,209,349,350,446,149,150,151,152,153,167,169,179,178,333,334,111,205";
        $parametry_dzisiejsze['date'] = "today";
        $cachedFixtures = "rozgrywki_dzisiejsze";

        if (!$zaplanowane = cache($cachedFixtures)) {
            $data['zaplanowaneNaDzis'] = $this->getFixtures($parametry_dzisiejsze);
            array_multisort(array_column($data['zaplanowaneNaDzis'], "competition_id"), SORT_ASC, $data['zaplanowaneNaDzis']);
            $zaplanowane = view('live/grajaDzis', $data, ['cache' => 900, 'cache_name' => $cachedFixtures]);         
        }
        return $zaplanowane;
    }

    public function wydarzeniaMeczu($numerekMeczu) {
        $cashedEvents = "wydarzenia_meczu_" . $numerekMeczu;
        $parametr['id'] = $numerekMeczu;
        
        if (!$wydarzenia = cache($cashedEvents)) {
            $data['event'] = $this->getEvents($parametr);
            $wydarzenia = view('live/gameEvents', $data, ['cache' => 60, 'cache_name' => $cashedEvents]);
        }
        return $wydarzenia;
    }

    public function zegarek() {
        if (!$zegarynka = cache('zegarek')) {
            $zegarynka = "Zapisany czas to " . date("H:i:s"); 
            cache()->save('zegarek', $zegarynka, 10);
        }

        echo $zegarynka;
        $teraz = date("Y-m-d H:i");
        echo "<p>" . $teraz . "</p>";

        $potem = date("Y-m-d H:i", strtotime($teraz . '+15 minutes'));
        echo "<p>" . $potem . "</p>";
    }

    public function komentarz() {
        $data["title"] = "Komentarz od autorski a zarazem dziennik";
        echo view('live/header', $data);
        echo view('live/komentarz', $data);
        echo view('live/footer', $data);
    }
}
?>