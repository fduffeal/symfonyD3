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

        die();

    }
}
