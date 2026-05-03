<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class SessionViewer extends Controller
{
    public function index()
    {
        $sessionPath = WRITEPATH . 'session/';
        $sessions = [];
        $searchKey = 'username'; // Klucz do wyszukiwania

        // Otwórz katalog sesji
        if ($handle = opendir($sessionPath)) {
            // Iteruj przez pliki sesji
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $filePath = $sessionPath . $file;

                    // Sprawdź datę modyfikacji pliku
                    if (date('Y-m-d', filemtime($filePath)) == date('Y-m-d')) {
                        $sessionData = file_get_contents($filePath);

                        // Sprawdź, czy dane sesji zawierają klucz 'username'
                        if (strpos($sessionData, $searchKey) !== false) {
                            $sessions[$file] = $this->parseSessionData($sessionData);
                        }
                    }
                }
            }
            closedir($handle);
        }

        echo '<pre>';
        print_r($sessions);
        echo '</pre>';
    }

    private function parseSessionData($data)
    {
        $sessionData = [];
        $parts = explode(';', $data);

        foreach ($parts as $part) {
            $keyValue = explode('|', $part);
            if (count($keyValue) == 2) {
                $sessionData[$keyValue[0]] = $keyValue[1];
            }
        }

        return $sessionData;
    }
}