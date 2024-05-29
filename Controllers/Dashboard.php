<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\UserModel;


class Dashboard extends BaseController
{
    public function index()
    {


        $userModel = new UserModel();
        $loggedInUserId = session()->get('loggedInUser');
        $userInfo = $userModel->find($loggedInUserId);


        $data = [
            'title' => 'Dashboard',
            'userInfo' => $userInfo,
        ];
        return view('dashboard/index', $data);
    }
}