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

        $bungie = $this->get('acme_es_battle.bungie');
        //$player = $bungie->getPlayer(2,'Fifoukiller84');

//        $test = $bungie->getPlayer(2,'Fifoukiller84');
        $test = $bungie->getAccount(2,'4611686018430647711');
//        $test = $bungie->getCharacters(2,'Fifoukiller84');

//        var_dump($player,$characters);

        echo json_encode($test);
        die();

    }


    /**
     * @param $character
     * @param \Acme\EsBattleBundle\Entity\User $user
     */
    private function _saveGameUserInfo($character,$user = null,$plaform = null,$game = null){
        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
         */
        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(
                array('ext_id' => $character['characterId'],'user' => $user)
            );

        if($userGame === null){
            $userGame = new UserGame();
        }

        $userGame->setGameProfilName($character['class'].' '.$character['level']);
        $userGame->setGameUsername($character['gamerTag']);
        $userGame->setData1($character['class']);
        $userGame->setData2($character['level']);
        $userGame->setData3($character['clan']);
        $userGame->setData4($character['backgroundPath']);
        $userGame->setData5($character['emblemPath']);
        $userGame->setExtId($character['characterId']);
        $userGame->setUser($user);
        $userGame->setPlateform($plaform);
        $userGame->setGame($game);

        $em = $this->getDoctrine()->getManager();
        $em->persist($userGame);
        $em->flush();

        return $userGame;
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

        foreach($characters as $key => $character){
            $userGame = $this->_saveGameUserInfo($character,$user,$plaform,$game);
            $characters[$key]["userGameId"] = $userGame->getId();
        }
//        var_dump($player,$characters);

        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(30);
        $response->setSharedMaxAge(30);
        $response->setContent(json_encode($characters));

        return $response;

    }
}
