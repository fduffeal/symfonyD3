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


        $stop_date = date('Y-m-d H:i:s', strtotime('-1 day', time()));

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT user
            FROM AcmeEsBattleBundle:User user
            WHERE user.onlineTime > :now'
        )->setParameter('now', $stop_date);

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
        $response->setMaxAge(30);
        $response->setSharedMaxAge(30);
        $response->setContent($json);

        return $response;
    }
}
