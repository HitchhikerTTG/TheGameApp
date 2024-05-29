<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\UserModel;

helper('filesystem');


class Archiwum extends BaseController
{
    public function index()
    {
    $map=directory_map('../writable/cache/');
    echo "<h3>What i really, really want is archiwum</h3>";
    foreach ($map as $wpis){

        $frag = substr($wpis, 0, 9);
        echo "<p>".$wpis. " => " .$frag."</p>";
        if ($frag=="live_mecz"){
        $cachedLive = $wpis;
        echo cache($cachedLive);
        

//        echo "<p><a href=\"\">$wpis</a></p>";
        }
        echo "aaa<br>";
    }
    
    
    
 //   echo "<pre>";
 //   print_r($map);
 //   echo "</pre>";
 //   echo "well wciąż jeszcze nie";
        
    }
}