<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserGame;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function addFriendAction($friendUsername,$username,$apikey){
        $response = new Response();
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $apikey)
            );

        if($user === null){
            $response->setStatusCode(401);
            return $response;
        }

        /**
         * @var \Acme\EsBattleBundle\Entity\User $friend
         */
        $friend = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $friendUsername)
            );

        if($user === null){
            $response->setStatusCode(404);
            $content = array('msg'=> 'friend not found');
            $response->setContent(json_encode($content));
            return $response;
        }

        $user->addFriend($friend);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
    }

    public function removeFriendAction($friendUsername,$username,$apikey){
        $response = new Response();
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $apikey)
            );

        if($user === null){
            $response->setStatusCode(401);
            return $response;
        }

        /**
         * @var \Acme\EsBattleBundle\Entity\User $friend
         */
        $friend = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $friendUsername)
            );

        if($user === null){
            $response->setStatusCode(404);
            $content = array('msg'=> 'friend not found');
            $response->setContent(json_encode($content));
            return $response;
        }

        $user->removeFriend($friend);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
    }

    public function getUsersAction(){

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT user,usergames, plateform, game
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames
            JOIN usergames.plateform plateform
            JOIN usergames.game game');

        $collection = $query->getResult();


        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        foreach($collection as $user){
            $aResult[] = $user->_toArray();
        }

        $json = json_encode($aResult);



        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(60);
        $response->setSharedMaxAge(60);
        $response->setContent($json);

        return $response;
    }

    public function getDestinyUsersGameAction($membershipType,$displayName){

        $response = new Response();

        $bungie = $this->get('acme_es_battle.bungie');

        $player = $bungie->getPlayer($membershipType,$displayName);
        if($player === null){
            return null;
        }
        $account = $bungie->getAccount($membershipType,$player->membershipId);

        $json= json_encode($account);
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(86400);
        $response->setSharedMaxAge(86400);
        $response->setContent($json);

        return $response;
    }
}
