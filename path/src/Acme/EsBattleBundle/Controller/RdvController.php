<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;



use Acme\EsBattleBundle\Entity\User;
use Acme\EsBattleBundle\Entity\Appointment;
use Acme\EsBattleBundle\Entity\Notification;
use Acme\EsBattleBundle\Entity\Tag;

class RdvController extends Controller
{
    public function indexAction()
    {


        $stop_date = date('Y-m-d H:i:s', strtotime('-1 day', time()));


        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT rdv
            FROM AcmeEsBattleBundle:Appointment rdv
            WHERE rdv.start > :now'
        )->setParameter('now', $stop_date);

        $collection = $query->getResult();

        $aResult = [];
        foreach($collection as $appointment){
            $aResult[] = $appointment->_toArray();
        }

        $json = json_encode($aResult);

	    $response = new Response();

        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(60);
        $response->setSharedMaxAge(60);
        $response->setContent($json);

        return $response;
    }

	public function createAction($plateform,$game,$tags,$description,$start,$duree,$nbParticipant,$userGameId,$username,$token)
	{

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $token)
            );

        if($user === null){
	        $response = new Response();
	        $response->setStatusCode(401);
	        return $response;
        }

        $myPlateform = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Plateform')
            ->findOneBy(
                array('id' => $plateform)
            );

        $myGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Game')
            ->findOneBy(
                array('id' => $game)
            );

        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(
                array('id' => $userGameId)
            );

		$startDay = new \DateTime();
		$startDay->setTimestamp($start);
		$nbParticipant = intval($nbParticipant);

        $aDuree = preg_split('/:/',$duree);

        $dateInterval = new \DateInterval('P0Y0M0DT'.$aDuree[0].'H'.$aDuree[1].'M0S');
        $endDay = new \DateTime();
        $endDay->setTimestamp($start);
        $endDay->add($dateInterval);

		$appointment = new Appointment();
		$appointment->setDescription($description);
		$appointment->setStart($startDay);
		$appointment->setEnd($endDay);
		$appointment->setDuree($duree);
		$appointment->setNbParticipant($nbParticipant);
        $appointment->setLeader($user);
        $appointment->addUsersGame($userGame);
        $appointment->setPlateform($myPlateform);
        $appointment->setGame($myGame);


        $aTags = preg_split("/[\s,]+/",$tags);

        $em = $this->getDoctrine()->getManager();

        foreach($aTags as $key){
            $selectedTag = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:Tag')
                ->findOneBy(array('nom' => $key));

	        $key = trim($key);

            if($selectedTag === null && $key !== ""){
                $selectedTag = new Tag();
                $selectedTag->setNom($key);
                $selectedTag->setPoids(0);
                $em->persist($selectedTag);
            }

            $appointment->addTag($selectedTag);
        }

		$em->persist($appointment);
		$em->flush();

        $json = $appointment->_toJson();


		$response = new Response();
		$response->setContent($json);
        return $response;

	}

	public function getRdvByIdAction($rdvId){

        $response = new Response();
        // Définit la réponse comme publique. Sinon elle sera privée par défaut.
        $response->setPublic();

		$appointment = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Appointment')
			->findOneBy(array('id'=>$rdvId));

        if($appointment === null){
            // définit l'âge max des caches privés ou des caches partagés
            $response->setMaxAge(3600);
            $response->setSharedMaxAge(3600);
            $response->setStatusCode(404);
            $content = array('msg'=> 'rdv not found');
            $response->setContent(json_encode($content));
            return $response;
        }


//		var_dump($appointment->getUpdated());
		//$response->setLastModified($appointment->getUpdated());
		$response->setETag('RDV_'.$appointment->getId().'_'.$appointment->getUpdated()->getTimestamp());

		// définit l'âge max des caches privés ou des caches partagés
		$response->setMaxAge(10);
		$response->setSharedMaxAge(10);

		// Vérifie que l'objet Response n'est pas modifié
		// pour un objet Request donné
		if ($response->isNotModified($this->getRequest())) {
			// Retourne immédiatement un objet 304 Response
			return $response;
		} else {
			$json = $appointment->_toJson();
			$response->setContent($json);
			return $response;
		}
	}

    public function joinRdvAction($rdvId,$userGameId,$username,$apikey){
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(array('username'=>$username,'apikey'=>$apikey));

        if($user === null){
	        $response = new Response();
	        $response->setStatusCode(401);
	        return $response;
        }


        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(
                array('id' => $userGameId)
            );

        $appointment->addUsersGameInQueue($userGame);
	    $appointment->setUpdatedValue();

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);
        $em->flush();

	    $notification = new Notification();
	    $notification->setCode($notification::NEW_PLAYER_JOIN);
	    $notification->setDestinataire($appointment->getLeader());
	    $notification->setExpediteur($user);
	    $notification->setAppointment($appointment);
	    $em->persist($notification);
	    $em->flush();

        $json = $appointment->_toJson();
	    $response = new Response();
	    $response->setContent($json);
	    return $response;
    }

    public function acceptUserAction($userGameId,$rdvId,$username,$apikey){

        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $leader = $appointment->getLeader();

        if($leader->getUsername() == $username && $leader->getApikey() == $apikey){

            $userGame = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:UserGame')
                ->findOneBy(array('id'=>$userGameId));

            $appointment->removeUsersGameInQueue($userGame);
            $appointment->addUsersGame($userGame);
	        $appointment->setUpdatedValue();

            $em = $this->getDoctrine()->getManager();
            $em->persist($appointment);
            $em->flush();


	        $notification = new Notification();
	        $notification->setCode($notification::YOU_HAVE_BEEN_ACCEPTED);
	        $notification->setDestinataire($userGame->getUser());
	        $notification->setExpediteur($leader);
	        $notification->setAppointment($appointment);
	        $em->persist($notification);
	        $em->flush();

            $json = $appointment->_toJson();
	        $response = new Response();
	        $response->setContent($json);
	        return $response;

        }else{
	        $response = new Response();
	        $response->setStatusCode(401);
	        return $response;
        }
    }

    public function kickUserAction($userGameId,$rdvId,$username,$apikey){
        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $leader = $appointment->getLeader();

        if($leader->getUsername() == $username && $leader->getApikey() == $apikey){
            $userGame = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:UserGame')
                ->findOneBy(array('id'=>$userGameId));

            $appointment->removeUsersGame($userGame);
	        $appointment->setUpdatedValue();

            $em = $this->getDoctrine()->getManager();
            $em->persist($appointment);
            $em->flush();

	        $notification = new Notification();
	        $notification->setCode($notification::YOU_HAVE_BEEN_KICKED);
	        $notification->setDestinataire($userGame->getUser());
	        $notification->setExpediteur($leader);
	        $notification->setAppointment($appointment);
	        $em->persist($notification);
	        $em->flush();

            $json = $appointment->_toJson();
	        $response = new Response();
	        $response->setContent($json);
	        return $response;

        }else {

	        $response = new Response();
	        $response->setStatusCode(401);
	        return $response;
        }
    }

    public function leaveRdvAction($rdvId,$userGameId,$username,$apikey){
        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(array('id'=>$userGameId));

        $user = $userGame->getUser();

        if($user->getUsername() !== $username || $user->getApikey() !== $apikey){
            return new Response(null, 401, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
        }
        $leader = $appointment->getLeader();
	    $hasNewLeader = false;
        if($user === $leader){
            $aUsersGame = $appointment->getUsersGame();
            foreach($aUsersGame as $userGameCanBeLeader){
                $userGameCanBeLeaderAccount = $userGameCanBeLeader->getUser();
                if($userGameCanBeLeaderAccount !== $leader){
                    $appointment->setLeader($userGameCanBeLeaderAccount);
                    $hasNewLeader = true;
                    break;
                }
            }

            if($hasNewLeader === false){
                $em = $this->getDoctrine()->getManager();
                $em->remove($appointment);
                $em->flush();

                return new Response(null, 308, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

            }
        }

        $appointment->removeUsersGame($userGame);
        $appointment->removeUsersGameInQueue($userGame);
	    $appointment->setUpdatedValue();

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);
        $em->flush();


	    $notification = new Notification();
	    if($hasNewLeader === false){
		    $notification->setCode($notification::ONE_USER_LEAVE);
		    $notification->setDestinataire($leader);
	    }else {
		    $notification->setCode($notification::LEADER_LEAVE_YOU_ARE_NEW_LEADER);
		    $notification->setDestinataire($appointment->getLeader());
	    }
	    $notification->setExpediteur($user);
	    $notification->setAppointment($appointment);
	    $em->persist($notification);
	    $em->flush();

        $json = $appointment->_toJson();

	    $response = new Response();
	    $response->setContent($json);
	    return $response;

    }

    public function promoteRdvAction($rdvId,$userGameId,$username,$apikey){

        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(array('id'=>$userGameId));

        $newLeader = $userGame->getUser();

        $oldLeader = $appointment->getLeader();

        if($oldLeader->getUsername() !== $username || $oldLeader->getApikey() !== $apikey){
            return new Response(null, 401, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
        }

        $appointment->setLeader($newLeader);
        $appointment->setUpdatedValue();

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);
        $em->flush();

	    $notification = new Notification();
        $notification->setCode($notification::YOU_HAVE_BEEN_PROMOTED);
	    $notification->setDestinataire($newLeader);
	    $notification->setExpediteur($oldLeader);
	    $notification->setAppointment($appointment);
	    $em->persist($notification);
	    $em->flush();

	    $json = $appointment->_toJson();

	    $response = new Response();
	    $response->setContent($json);
        return $response;


    }

	/**
	 * @return Response
	 */
	public function getNotificationsAction(){
		$stop_date = date('Y-m-d H:i:s', strtotime('-5 hour', time()));

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT notifications
            FROM AcmeEsBattleBundle:Notification notifications
            JOIN notifications.expediteur expediteur
            JOIN notifications.destinataire destinataire
            WHERE notifications.created > :stop_date'
		)->setParameter('stop_date', $stop_date);

		$collection = $query->getResult();

        $aNotification = array();
        foreach($collection as $key => $notification){
            $aNotification[$key] = $notification->_toArray();
        }

		$response = new Response();
		$response->setContent(json_encode($aNotification));
		$response->setPublic();
		// définit l'âge max des caches privés ou des caches partagés
		$response->setMaxAge(20);
		$response->setSharedMaxAge(20);

		return $response;
	}

    public function getFormInfoAction(){

        $poidsMini = 0;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT tags
            FROM AcmeEsBattleBundle:Tag tags
            WHERE tags.poids > :poids_mini'
        )->setParameter('poids_mini', $poidsMini);

        $tags = $query->getResult();

        $aTags = array();
        foreach($tags as $tag){
            $aTags[] = $tag->_toArray();
        }

        $plateforms = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Plateform')
            ->findAll();

        $aPlateforms = array();
        foreach($plateforms as $plateform){
            $aPlateforms[] = $plateform->_toArray();
        }

        $games = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Game')
            ->findAll();

        $aGame = array();
        foreach($games as $game){
            $aGame[] = $game->_toArray();
        }

        $result = array(
            'tags' => $aTags,
            'plateforms' => $aPlateforms,
            'games' => $aGame
        );
        $json = json_encode($result);
        $response = new Response();
	    $response->setContent($json);
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(600);
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function createMatchmakingAction($matchmakingId,$username,$apikey){
        $response = new Response();

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey'=>$apikey)
            );

        if($user === null){
            $response->setStatusCode(401);
            return $response;
        }

        $matchmaking = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Matchmaking')
            ->findOneBy(
                array('id' => $matchmakingId)
            );

        if($matchmaking === null){
            $response->setStatusCode(401);
            return $response;
        }
    }
}
