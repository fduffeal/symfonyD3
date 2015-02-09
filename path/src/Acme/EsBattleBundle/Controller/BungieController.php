<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserGame;
use Foo\Bar\B;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Acme\EsBattleBundle\Bungie;

class BungieController extends Controller
{

    public function indexAction(){

        $membershipType = 2;
        $destinyMembershipId = '4611686018430647711';
        $characterId = "2305843009215026244";

        $bungie = $this->get('acme_es_battle.bungie');
        //$player = $bungie->getPlayer(2,'Fifoukiller84');

//        $test = $bungie->getPlayer(2,'Fifoukiller84');
//        $test = $bungie->getCharacters(2,'Fifoukiller84');

//        var_dump($player,$characters);


//        $test = $bungie->getAccount(2,$destinyMembershipId);

//        $test = $bungie->getCharacter(2,$destinyMembershipId,$characterId);

//        $test = $bungie->getItems();

        $test = $bungie->getCharacterInventory($membershipType,$destinyMembershipId,$characterId);

        echo json_encode($test);
        die();

    }

    /*
     * $plateform 1 > Xbox, 2 > Playstation
     */
    public function getCharactersAction($plateformId,$plateformBungie,$gamerTag,$username,$apikey){

        $response = new Response();

        $bungie = $this->get('acme_es_battle.bungie');

        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = null;
        if($username !== 'null' && $apikey !== 'null'){
            $user = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:User')
                ->findOneBy(
                    array('username' => $username,'apikey' => $apikey)
                );
        }

        /**
         * @var \Acme\EsBattleBundle\Entity\Plateform $plaform
         */
        $plaform = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Plateform')
            ->findOneBy(
                array('id' => $plateformId)
            );

        /**
         * @var \Acme\EsBattleBundle\Entity\Game $game
         */
        $game = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Game')
            ->findOneBy(
                array('id' => $bungie->getDestinyGameId())
            );


        $characters = $bungie->getCharacters($plateformBungie,$gamerTag);
        if($characters === null){
            $response->setStatusCode(403);
            $content = array('msg'=> 'characters not found');
            $response->setContent(json_encode($content));
            return $response;
        }

        $aUserGame = [];
        foreach($characters as $key => $character){
            $userGame = $bungie->saveGameUserInfo($character,$user,$plaform,$game);
            $aUserGame[] = $userGame->_toArray();
            //$characters[$key]["userGameId"] = $userGame->getId();
        }
//        var_dump($player,$characters);

        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(30);
        $response->setSharedMaxAge(30);
        //$response->setContent(json_encode($characters));
        $response->setContent(json_encode($aUserGame));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }
}
