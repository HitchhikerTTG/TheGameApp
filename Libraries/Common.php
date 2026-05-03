<?php namespace App\Libraries;

use App\Controllers\BaseController;
use App\Models\TerminarzModel;
use App\Models\TurniejeModel;

class Common
{
    public function sharedFunction()
    {
        return "This is a shared function.";
    }

    protected $_key;
    protected $_secret;
    public $connection = null;
    protected $_baseUrl = "https://livescore-api.com/api-client/";

    public function __construct()
    {
        helper(['url', 'form']);
    }

    protected function _buildUrl($endpoint, $params) {
        $params['key'] = $_ENV['lskey'];
        $params['secret'] = $_ENV['lsscr'];
        return $this->_baseUrl . $endpoint . '?' . http_build_query($params);
    }

    public function getFixtures($params = []) {
    $url = $this->_buildUrl('fixtures/matches.json', $params);
    $data = $this->_makeRequest($url);

    // Logowanie danych zwróconych przez _makeRequest
    //$file = WRITEPATH . 'logs/test_log.log';
    //file_put_contents($file, "Zwrócone dane: " . print_r($data, true) . "\n", FILE_APPEND);
    
    return $data;
}

    public function getGroups($params = []) {
        $url = $this->_buildUrl('competitions/groups.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function getTeams($params = []){
        $url = $this->_buildUrl('competitions/participants.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function captureTheFlag($params = []) {
        $url = $this->_buildUrl('countries/flag.json', $params);
        $data = $this->_makeRequestForTheFlag($url);
        return $data;
    }

    protected function _makeRequestForTheFlag($url) {
        return $this->_makeRequest($url);
    }

private function _makeRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Logowanie odpowiedzi API i kodu HTTP
    $file = WRITEPATH . 'logs/test_log.log';
    //file_put_contents($file, "HTTP Code: " . $httpCode . "\n", FILE_APPEND);
    //file_put_contents($file, "API Response: " . $response . "\n", FILE_APPEND);

    if ($httpCode !== 200) {
        log_message('error', 'Błąd połączenia z API: ' . $httpCode);
        return null;
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', 'Błąd dekodowania JSON: ' . json_last_error_msg());
        return null;
    }

    return $data;
}

    public function loadTournaments() {
        $turniejModel = new TurniejeModel();
        $turnieje = $turniejModel->findAll();
        $daneDoPrzekazania = [];

        if ($turnieje) {
            foreach ($turnieje as $turniej) {
                $daneDoPrzekazania[] = [
                    'ID' => $turniej['id'],
                    'CompetitionID' => $turniej['CompetitionID'],
                    'CompetitionName' => $turniej['CompetitionName'],
                    'Active' => $turniej['Active']
                ];
            }
        } else {
            $daneDoPrzekazania = "W kwestii turniejów nie mam nic do pokazania";
        }

        return $daneDoPrzekazania;
    }

    function custom_log($message) {
    $file = WRITEPATH . 'logs/custom_log.log';
    
    // Sprawdź, czy katalog istnieje, jeśli nie, utwórz go
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    
    // Sprawdź, czy plik istnieje, jeśli nie, utwórz go
    if (!file_exists($file)) {
        file_put_contents($file, ''); // Utwórz pusty plik
    }
    
    // Odczytaj zawartość pliku
    $current = file_get_contents($file);
    
    // Dodaj nową wiadomość do logu
    $current .= "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    
    // Zapisz zaktualizowaną zawartość do pliku
    file_put_contents($file, $current);
}
}
?>