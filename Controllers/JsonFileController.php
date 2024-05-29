<?php namespace App\Controllers;

class JsonFileController extends BaseController
{
    public function serveJson($turniejID, $meczID)
    {
        $filePath = WRITEPATH . "mecze/{$turniejID}/{$meczID}.json";
        if (file_exists($filePath)) {
            $this->response
                ->setContentType('application/json')
                ->setBody(file_get_contents($filePath))
                ->send();
            exit;
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function serveTypy($meczID)
    {
        $filePath = WRITEPATH . "typy/{$meczID}.json";
        if (file_exists($filePath)) {
            $this->response
                ->setContentType('application/json')
                ->setBody(file_get_contents($filePath))
                ->send();
            exit;
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
}