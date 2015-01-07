<?php

namespace Acme\EsBattleBundle\Controller;

use Foo\Bar\B;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Acme\EsBattleBundle\Bungie;

class BungieController extends Controller
{

    public function indexAction(){

        $bungie = $this->get('acme_es_battle.bungie');
        $player = $bungie->SearchDestinyPlayer(2,'Fifoukiller84');

        var_dump($player);
        die();

    }
    public function searchDestinyPlayerAction($membershipType,$displayName){

        $ch = curl_init("http://www.example.com/");
        $fp = fopen("example_homepage.txt", "w");

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
