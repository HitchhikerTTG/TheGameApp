<?php


namespace App\Libraries;


class Token 
{
    // Encrypt user password.
    
    public static function createToken($forID,$reason)
    {
        $tokenModel = new \App\Models\TokenModel();
        //Tworzymy nowy token
        
        $token = bin2hex(random_bytes(12));
        $teraz=date("Y-m-d H:i");
        $koniec=date("Y-m-d H:i",strtotime($teraz.'+17 minutes'));



        $tokenData = [
        'RequestorUNIID'=>$forID,
        'CreatedAT' => $teraz,
        'ValidUntil'=>$koniec,
        'Valid'=> 1,
        'Reason'=> $reason,
        'Token'=>$token,
        ];
        
        $tokenModel->insert($tokenData);
        
        return $token;
    }


    // Check user password with db password.


    public static function check($userPassword, $dbUserPassword)
    {
        if(password_verify($userPassword, $dbUserPassword))
        {
            return true;
        }


        return false;
    }
}