<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserGame;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;
use Acme\EsBattleBundle\Entity\Notification;

use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

    public function getFriendAction($username,$apikey){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

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


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT friends
            FROM AcmeEsBattleBundle:User friends
            JOIN friends.friendsWithMe user
            JOIN friends.friends user2
            WHERE user.id = :userId and user2.id = :userId'
        )->setParameter('userId',$user->getId());

        $friends = $query->getResult();

        $aData = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $friend
         */
        foreach($friends as $key => $friend){
            $aData[] = $friend->_toArray();
        }

        $response->setContent(json_encode($aData));

        return $response;
    }

    public function getMyFriendRequestAction($userId){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT friends
            FROM AcmeEsBattleBundle:User friends
            JOIN friends.friendsWithMe user
            WHERE user.id  = :userId and friends.id NOT IN (
              SELECT myfriends.id FROM AcmeEsBattleBundle:User myfriends
              JOIN myfriends.friends me where me.id = :userId
            )'
        )->setParameter('userId',$userId);

        $friends = $query->getResult();

        $aData = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $friend
         */
        foreach($friends as $key => $friend){
            $aData[] = $friend->_toArrayShort();
        }

        $response->setContent(json_encode($aData));

        return $response;
    }

    public function getFriendRequestAction($userId){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT friends
            FROM AcmeEsBattleBundle:User friends
            JOIN friends.friends user
            WHERE user.id  = :userId and friends.id NOT IN (
              SELECT myfriends.id FROM AcmeEsBattleBundle:User myfriends
              JOIN myfriends.friendsWithMe me where me.id = :userId
            )'
        )->setParameter('userId',$userId);

        $friends = $query->getResult();

        $aData = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $friend
         */
        foreach($friends as $key => $friend){
            $aData[] = $friend->_toArrayShort();
        }

        $response->setContent(json_encode($aData));

        return $response;
    }

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

        if($friend === null){
            $response->setStatusCode(404);
            $content = array('msg'=> 'friend not found');
            $response->setContent(json_encode($content));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT user,friends
            FROM AcmeEsBattleBundle:User user
            JOIN user.friends friends
            WHERE friends.id = :friendId AND user.id = :userId";

        $query = $em->createQuery($dql)
            ->setParameter('friendId', $friend->getId())
            ->setParameter('userId', $user->getId());

        $alreadyFriend = $query->getResult();

        if($alreadyFriend !== null){
            $user->addFriend($friend);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

	        /**
	         * @var \Acme\EsBattleBundle\Entity\UserGame $firstUsergames
	         */
	        $aUsergames = $user->getUsergames();
	        $firstUsergames = $aUsergames[0];
	        $gameUsername = $firstUsergames->getGameUsername();



	        $url = 'http://lfg.esbattle.com/fr/users/connected?user='.$gameUsername;

	        $message = \Swift_Message::newInstance()
		        ->setContentType('text/html')
		        ->setSubject("Demande d'amis sur Esbattle.com")
		        ->setFrom('contact.esbattle@gmail.com')
		        ->setTo($friend->getEmail())
		        ->setBody($this->renderView('AcmeEsBattleBundle:Mail:request-friends.html.twig',array('username' => $friend->getUsername(),'from'=>$user->getUsername(),'url'=>$url)));
	        $this->get('mailer')->send($message);
        }


        $response->headers->set('Content-Type', 'application/json');
        $response = $this->forward('AcmeEsBattleBundle:User:getFriend', array(
            'username'  => $username,
            'apikey'  => $apikey
        ));

        $notification = new Notification();
        $notification->setCode($notification::ADD_FRIEND);
        $notification->setDestinataire($friend);
        $notification->setExpediteur($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();

        return $response;
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

        $response->headers->set('Content-Type', 'application/json');
        $response = $this->forward('AcmeEsBattleBundle:User:getFriend', array(
            'username'  => $username,
            'apikey'  => $apikey
        ));

        return $response;
    }

    public function getUsersAction(){

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setSharedMaxAge(600);

        $em = $this->getDoctrine()->getManager();

//        $query = $em->createQuery(
//            'SELECT user
//            FROM AcmeEsBattleBundle:User user
//            ORDER BY user.created DESC')
//            ->setMaxResults(1);
//
//        $result = $query->getResult();
//
//        if(!$result[0]){
//            return $response;
//        }
//
//        $response->setLastModified($result[0]->getCreated());
//
//        // Vérifie que l'objet Response n'est pas modifié
//        // pour un objet Request donné
//        if ($response->isNotModified($this->getRequest())) {
//            // Retourne immédiatement un objet 304 Response
//            return $response;
//        }

        $query = $em->createQuery(
            'SELECT user
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
        $response->setContent($json);

        return $response;
    }

    public function getUsersByPageAction($page,$nbResult){


        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(600);
        $response->setSharedMaxAge(600);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT user
            FROM AcmeEsBattleBundle:User user
            ORDER BY user.created DESC')
            ->setMaxResults(1);

        $result = $query->getResult();

        if(!$result[0]){
            return $response;
        }

        $response->setLastModified($result[0]->getCreated());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

        $nbResult = intval($nbResult);

        $start = (intval($page)-1)*$nbResult;


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT user,usergames, plateform, game
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames
            JOIN usergames.plateform plateform
            JOIN usergames.game game')
            ->setFirstResult($start)
            ->setMaxResults($nbResult);

        $collection = $query->getResult();

        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        foreach($collection as $user){
            $aResult[] = $user->_toArray();
        }

        $json = json_encode($aResult);
        $response->setContent($json);

        return $response;
    }

    public function getUsersByPlateformAction($plateformId,$page,$nbResult){


        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(600);
        $response->setSharedMaxAge(600);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT user
            FROM AcmeEsBattleBundle:User user
            ORDER BY user.created DESC')
            ->setMaxResults(1);

        $result = $query->getResult();

        if(!$result[0]){
            return $response;
        }

        $response->setLastModified($result[0]->getCreated());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

        $nbResult = intval($nbResult);

        $start = (intval($page)-1)*$nbResult;


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT user,usergames, plateform, game
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames
            JOIN usergames.plateform plateform
            JOIN usergames.game game
            WHERE plateform.id = :plateformId')
            ->setParameter('plateformId',$plateformId)
            ->setFirstResult($start)
            ->setMaxResults($nbResult);

        $collection = $query->getResult();

        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        foreach($collection as $user){
            $aResult[] = $user->_toArray();
        }

        $json = json_encode($aResult);
        $response->setContent($json);

        return $response;
    }

    public function searchUserAction($username,$plateformId,$page,$nbResult){


        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setSharedMaxAge(600);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT user
            FROM AcmeEsBattleBundle:User user
            ORDER BY user.onlineTime DESC')
            ->setMaxResults(1);

        $result = $query->getResult();

        if(!$result[0]){
            return $response;
        }

        $response->setLastModified($result[0]->getOnlineTime());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

        $nbResult = intval($nbResult);

        $start = (intval($page)-1)*$nbResult;

        $em = $this->getDoctrine()->getManager();

        if($plateformId !== 'null' && $username !== 'null'){
            $query = $em->createQuery(
                'SELECT user,usergames, plateform, game
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames
            JOIN usergames.plateform plateform
            JOIN usergames.game game
            WHERE plateform.id = :plateformId and usergames.game_username LIKE :usernameSearch
            ORDER BY user.onlineTime DESC')
                ->setParameter('plateformId',$plateformId)
                ->setParameter('usernameSearch','%'.$username.'%')
                ->setFirstResult($start)
                ->setMaxResults($nbResult);
        } else if ($plateformId !== 'null'){
            $query = $em->createQuery(
                'SELECT user,usergames, plateform, game
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames
            JOIN usergames.plateform plateform
            JOIN usergames.game game
            WHERE plateform.id = :plateformId
            ORDER BY user.onlineTime DESC')
                ->setParameter('plateformId',$plateformId)
                ->setFirstResult($start)
                ->setMaxResults($nbResult);
        } else {
            $query = $em->createQuery(
                'SELECT user,usergames, plateform, game
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames
            JOIN usergames.plateform plateform
            JOIN usergames.game game
            ORDER BY user.onlineTime DESC')
                ->setFirstResult($start)
                ->setMaxResults($nbResult);
        }


        $collection = $query->getResult();

        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        foreach($collection as $user){
            $aResult[] = $user->_toArray();
        }

        $json = json_encode($aResult);
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
        $response->headers->set('Content-Type', 'application/json');


        return $response;
    }
}
