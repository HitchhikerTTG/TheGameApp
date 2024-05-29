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
        $params['key'] =  $_ENV['lskey'];
        $params['secret'] = $_ENV['lsscr'];
        return $this->_baseUrl . $endpoint . '?' . http_build_query($params);
    }

	public function getFixtures($params = []) {
        $url = $this->_buildUrl('fixtures/matches.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function getGroups($params = []) {
        $url = $this->_buildUrl('competitions/groups.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function getTeams ($params = []){
        $url = $this->_buildUrl('competitions/participants.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function captureTheFlag($params = []){
        $url = $this->_buildUrl('countries/flag.json', $params);
        $data = $this->_makeRequestForTheFlag($url);
        return $data;
    }

    	protected function _makeRequestForTheFlag($url) {
        #$json = $this->_useCache($url);

        #if ($json) {
        #   $data = json_decode($json, true);
        #} else {
            $json = file_get_contents($url);
            $data = json_decode($json, true);

        #   if (!$data['success']) {
        #       throw new RuntimeException($data['error']);
        #   }
        #
        #   $this->_saveCache($url, $json);
        #}

        return $data;
    }


	protected function _makeRequest($url) {
        #$json = $this->_useCache($url);

        #if ($json) {
        #   $data = json_decode($json, true);
        #} else {
            $json = file_get_contents($url);
            $data = json_decode($json, true);

        #   if (!$data['success']) {
        #       throw new RuntimeException($data['error']);
        #   }
        #
        #   $this->_saveCache($url, $json);
        #}

        return $data['data'];
    }

    public function loadTournaments(){
    $turniejModel = new TurniejeModel();
    // $warunki = $turniejModel->builder(); // To nie jest potrzebne, jeśli chcesz pobrać wszystkie rekordy.
   // Jeśli chcesz pobrać wszystkie wiersze z tabeli:
    $turnieje = $turniejModel->findAll();
    $daneDoPrzekazania=[];
    if ($turnieje) {
        // Przygotuj dane do przekazania, ale bez tworzenia HTML
        $daneDoPrzekazania = [];
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

}