
<?php

namespace App\Controllers;

class DataFetcher extends BaseController
{
    public function index()
    {
        // Example data fetch using CodeIgniter's CURLRequest
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->request('GET', 'https://api.example.com/data');
            $data['content'] = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $data['content'] = ['error' => $e->getMessage()];
        }
        
        return view('datafetcher/display', $data);
    }
}
