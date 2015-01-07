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
        //$player = $bungie->getPlayer(2,'Fifoukiller84');

        $characters = $bungie->getCharactersNew(2,'Fifoukiller84');

//        var_dump($player,$characters);

        echo json_encode($characters);
        die();

    }

    /*
     * $plateform 1 > Xbox, 2 > Playstation
     */
    public function getCharactersAction($plateform,$gamerTag){

        $bungie = $this->get('acme_es_battle.bungie');

        $characters = $bungie->getCharacters(2,$gamerTag);
//        var_dump($player,$characters);

        echo json_encode($characters);
        die();

    }
}
