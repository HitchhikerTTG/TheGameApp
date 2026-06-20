<?php

namespace App\Controllers;

class LiveScore extends BaseController
{
    protected $_key;
    protected $_secret;
    public $connection = null;
    protected $_baseUrl = "https://livescore-api.com/api-client/";

    protected function _buildUrl($endpoint, $params) {
        $params['key']    = $_ENV['lskey'];
        $params['secret'] = $_ENV['lsscr'];
        return $this->_baseUrl . $endpoint . '?' . http_build_query($params);
    }

    public function getLivescores($params = []) {
        $url  = $this->_buildUrl('scores/live.json', $params);
        $data = $this->_makeRequest($url);
        foreach ($data['match'] as &$mecz) {
            $parametry['id']       = $mecz['id'];
            $eventurl              = $this->_buildUrl('scores/events.json', $parametry);
            $mecz['wydarzenia']    = $this->_makeRequest($eventurl);
        }
        return $data['match'];
    }

    // Tylko wyniki -- bez dodatkowych requestów na eventy
    public function getLivescoresSimple($params = []): array {
        $url  = $this->_buildUrl('scores/live.json', $params);
        $data = $this->_makeRequest($url);
        return $data['match'] ?? [];
    }

    public function getFixtures($params = []) {
        $url  = $this->_buildUrl('fixtures/matches.json', $params);
        $data = $this->_makeRequest($url);
        return $data['fixtures'];
    }

    public function getEvents($params = []) {
        $url  = $this->_buildUrl('scores/events.json', $params);
        $data = $this->_makeRequest($url);
        CLI::write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'orange');
        return $data['event'] ?? []; 
    }

    public function getHTH($params = []) {
        $url  = $this->_buildUrl('teams/head2head.json', $params);
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
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);

        $this->logApiRequest();

        $data = json_decode($response, true);
        return $data['data'];
    }

    private function logApiRequest(): void {
        $path  = WRITEPATH . 'api_counter.json';
        $today = date('Y-m-d');
        $data  = file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
        $data[$today] = ($data[$today] ?? 0) + 1;
        // Trzymaj tylko ostatnie 30 dni
        if (count($data) > 30) {
            $data = array_slice($data, -30, null, true);
        }
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function test() {
        $data['title'] = "Tu się będzie dziać coś strasznego";
        return view('live/header', $data)
             . view('live/test', $data)
             . view('live/footer', $data);
    }

    public function preMecz($mecz, $druzyna1, $druzyna2) {
        $cashedPreMecz = "PreMecz_" . $mecz;
        $parametry = ['team1_id' => $druzyna1, 'team2_id' => $druzyna2];
        if (!$preMecz = cache($cashedPreMecz)) {
            $data['h2h'] = $this->getHTH($parametry);
            $preMecz = view('live/eksperyment', $data, ['cache' => 60, 'cache_name' => $cashedPreMecz]);
        }
        return $preMecz;
    }

    public function index() {
        $data['title'] = "Wyniki najciekawszych meczów piłkarskich";
        echo view('live/header', $data);
        echo $this->naZywo();
        echo $this->zaplanowaneNaDzis();
        echo view('live/footer', $data);
        echo view('live/skrypty', $data);
    }

    public function naZywo() {
        $parametry['competition_id'] = "362,363,387,274,271,227,244,245,1,2,3,4,60,209,349,350,446,149,150,151,152,153,167,169,179,178,333,334,111,205";
        $cachedLive = "live_general";
        if (!$live = cache($cachedLive)) {
            $data['live'] = $this->getLivescores($parametry);
            $live = view('live/naZywo', $data, ['cache' => 60, 'cache_name' => $cachedLive]);
        }
        return $live;
    }

    public function zaplanowaneNaDzis() {
        $parametry['competition_id'] = "363,387,274,271,227,244,245,1,2,3,4,60,209,349,350,446,149,150,151,152,153,167,169,179,178,333,334,111,205";
        $parametry['date'] = "today";
        $cachedFixtures = "rozgrywki_dzisiejsze";
        if (!$zaplanowane = cache($cachedFixtures)) {
            $data['zaplanowaneNaDzis'] = $this->getFixtures($parametry);
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
        echo "<p>" . date("Y-m-d H:i") . "</p>";
    }

    public function komentarz() {
        $data["title"] = "Komentarz od autorski a zarazem dziennik";
        echo view('live/header', $data);
        echo view('live/komentarz', $data);
        echo view('live/footer', $data);
    }
    public function getHistory(array $params = []): array {
    $url  = $this->_buildUrl('scores/history.json', $params);
    $data = $this->_makeRequest($url);
    return $data['match'] ?? [];
    }

}
