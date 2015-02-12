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

use Symfony\Component\Config\Definition\Exception\Exception;

class RdvController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT rdv
            FROM AcmeEsBattleBundle:Appointment rdv
            ORDER BY rdv.updated DESC'
        )->setMaxResults(1);

        $collection = $query->getResult();

        $response = new Response();
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(30);
        $response->setSharedMaxAge(30);
        $response->headers->set('Content-Type', 'application/json');

        if(!$collection[0]){
            return $response;
        }

        $response->setLastModified($collection[0]->getUpdated());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

        $now = date('Y-m-d H:i:s');

        $query = $em->createQuery(
            'SELECT rdv,usersGame, tags, plateform, game, user, leader
            FROM AcmeEsBattleBundle:Appointment rdv
            JOIN rdv.usersGame usersGame
            JOIN usersGame.user user
            JOIN rdv.plateform plateform
            JOIN rdv.game game
            JOIN rdv.tags tags
            JOIN rdv.leader leader
            where rdv.end > :now'
        )->setParameter('now', $now);

        $collection = $query->getResult();


        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
         */
        foreach($collection as $appointment){
            $aResult[] = $appointment->_toArrayShort();
        }

        $json = json_encode($aResult);

//        throw new Exception();

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

		/**
		 * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
		 */
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

            if($selectedTag !== null){
                $appointment->addTag($selectedTag);
            }
        }

		/**
		 * on supprime les autres lien à un rdv avant de sauvegarder le nouveau
		 */
		$this->removeUserGameInAllOtherGameInSameTime($userGame,$appointment);

		$em->persist($appointment);
		$em->flush();

        $json = $appointment->_toJson();


		$response = new Response();
        $response->headers->set('Content-Type', 'application/json');
		$response->setContent($json);
        return $response;

	}

	public function getRdvByIdAction($rdvId){

        $response = new Response();
        // Définit la réponse comme publique. Sinon elle sera privée par défaut.
        $response->setPublic();
        $response->headers->set('Content-Type', 'application/json');

		/**
		 * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
		 */
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

		/**
		 * @var \Acme\EsBattleBundle\Entity\Matchmaking $matchmaking
		 */
		$matchmaking = $appointment->getMatchmaking();

		if($matchmaking !== null){
			$now = new \DateTime();
			$time = $now->getTimestamp();

			$usersGameCollection = $appointment->getUsersGame();

			$bAppointmentNeedUpdate = false;
			/**
			 * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
			 */
			foreach($usersGameCollection as $userGame){
				/**
				 * @var \Acme\EsBattleBundle\Entity\User $user
				 */
				$user = $userGame->getUser();
				if($time > $user->getOnlineTime()->getTimestamp() + 5 * 60){
					$appointment->removeUsersGame($userGame);
					$bAppointmentNeedUpdate = true;
				}

			}

			if($bAppointmentNeedUpdate === true){
				$em = $this->getDoctrine()->getManager();
				$em->persist($appointment);
				$em->flush();
			}
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

    /**
     * joinRdvAction : rejoindre une partie depuis la page party/waiting (peut afficher des rdv ou matchmaking)
     * @param $rdvId
     * @param $userGameId
     * @param $username
     * @param $apikey
     * @return Response
     */
    public function joinRdvAction($rdvId,$userGameId,$username,$apikey){
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(array('username'=>$username,'apikey'=>$apikey));

        if($user === null){
	        $response = new Response();
	        $response->setStatusCode(401);
	        return $response;
        }

	    /**
	     * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
	     */
        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(
                array('id' => $userGameId)
            );

        $isMatchmaking = ($appointment->getMatchmaking() !== null);

        $collectionUsersGame = $appointment->getUsersGame();
        /**
         * il reste de la place on ajoute à la liste
         */
        if(sizeof($collectionUsersGame) < $appointment->getNbParticipant()){
            $response = $this->forward('AcmeEsBattleBundle:Rdv:addUserGameInAppointment', array(
                'userGame'  => $userGame,
                'appointment'  => $appointment
            ));
        /**
         * plus de place on ajoute à la file d'attente
         */
        } else{
            $response = $this->forward('AcmeEsBattleBundle:Rdv:addUserInQueue', array(
                'userGame'  => $userGame,
                'appointment'  => $appointment
            ));
        }

        if($isMatchmaking === false){
            $notification = new Notification();
            $notification->setCode($notification::NEW_PLAYER_JOIN);
            $notification->setDestinataire($appointment->getLeader());
            $notification->setExpediteur($user);
            $notification->setAppointment($appointment);
            $em = $this->getDoctrine()->getManager();
            $em->persist($notification);
            $em->flush();
        }


	    return $response;
    }

    public function acceptUserAction($userGameId,$rdvId,$username,$apikey){

        /**
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
         */
        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $leader = $appointment->getLeader();

        if($leader->getUsername() == $username && $leader->getApikey() == $apikey){

            $userGame = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:UserGame')
                ->findOneBy(array('id'=>$userGameId));

	        $response = $this->forward('AcmeEsBattleBundle:Rdv:addUserGameInAppointment', array(
		        'userGame'  => $userGame,
		        'appointment'  => $appointment
	        ));

	        $notification = new Notification();
	        $notification->setCode($notification::YOU_HAVE_BEEN_ACCEPTED);
	        $notification->setDestinataire($userGame->getUser());
	        $notification->setExpediteur($leader);
	        $notification->setAppointment($appointment);
	        $em = $this->getDoctrine()->getManager();
	        $em->persist($notification);
	        $em->flush();

	        return $response;

        }else{
	        $response = new Response();
	        $response->setStatusCode(401);
	        return $response;
        }
    }

    /**
     * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
     * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
     */
    public function addUserInQueueAction($appointment,$userGame){

        $appointment->removeUsersGame($userGame);
        $appointment->addUsersGameInQueue($userGame);
        $appointment->setUpdatedValue();

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);
        $em->flush();

        $json = $appointment->_toJson();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($json);
        return $response;
    }

    public function kickUserAction($userGameId,$rdvId,$username,$apikey){
        /**
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
         */
        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $leader = $appointment->getLeader();

        if($leader->getUsername() == $username && $leader->getApikey() == $apikey){
            $userGame = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:UserGame')
                ->findOneBy(array('id'=>$userGameId));

            $response = $this->forward('AcmeEsBattleBundle:Rdv:addUserInQueue', array(
                'userGame'  => $userGame,
                'appointment'  => $appointment
            ));

            $notification = new Notification();
            $notification->setCode($notification::YOU_HAVE_BEEN_KICKED);
            $notification->setDestinataire($userGame->getUser());
            $notification->setExpediteur($leader);
            $notification->setAppointment($appointment);
            $em = $this->getDoctrine()->getManager();
            $em->persist($notification);
            $em->flush();

        }else {
	        $response = new Response();
	        $response->setStatusCode(401);
        }
        return $response;
    }

    public function leaveRdvAction($rdvId,$userGameId,$username,$apikey){
	    $response = new Response();

        $response->headers->set('Content-Type', 'application/json');

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

        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(array('id'=>$userGameId));

        $user = $userGame->getUser();

        if($user->getUsername() !== $username || $user->getApikey() !== $apikey){
            $response->setStatusCode(401);
            return $response;
        }
        $leader = $appointment->getLeader();
	    $hasNewLeader = false;
        if($user === $leader){
            $hasNewLeader = $this->setNewLeader($appointment);

//            if($hasNewLeader === false){
//                $em = $this->getDoctrine()->getManager();
//                $em->remove($appointment);
//                $em->flush();
//
//                $response->setStatusCode(308);
//                return $response;
//
//            }
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

	    $response->setContent($json);
	    return $response;

    }

    public function promoteRdvAction($rdvId,$userGameId,$username,$apikey){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(array('id'=>$userGameId));

        $newLeader = $userGame->getUser();

        $oldLeader = $appointment->getLeader();

        if($oldLeader->getUsername() !== $username || $oldLeader->getApikey() !== $apikey){
            $response->setStatusCode(401);
            return $response;
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

	    $response->setContent($json);
        return $response;


    }

	/**
	 * @return Response
	 */
	public function getNotificationsAction(){

        $stop_date = date('Y-m-d H:i:s', strtotime('-5 hour', time()));
        $aNotification = array();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(20);
        $response->setSharedMaxAge(20);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT notifications
            FROM AcmeEsBattleBundle:Notification notifications
            WHERE notifications.created > :stop_date'
        )->setParameter('stop_date', $stop_date)->setMaxResults(1);

        $result = $query->getResult();

        if(!$result){
            $date = new \DateTime();
            $response->setLastModified($date);
            $response->setContent(json_encode($aNotification));
            return $response;
        }

        $response->setLastModified($result[0]->getCreated());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT notifications, expediteur, destinataire
            FROM AcmeEsBattleBundle:Notification notifications
            JOIN notifications.expediteur expediteur
            JOIN notifications.destinataire destinataire
            WHERE notifications.created > :stop_date'
		)->setParameter('stop_date', $stop_date);

		$collection = $query->getResult();


        foreach($collection as $key => $notification){
            $aNotification[$key] = $notification->_toArray();
        }

        $response->setContent(json_encode($aNotification));

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
        $response->headers->set('Content-Type', 'application/json');


        return $response;
    }


    /**
     * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
     * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
     */
    public function addUserGameInAppointmentAction($userGame,$appointment){

        $this->removeUserGameInAllOtherGameInSameTime($userGame,$appointment);

        $appointment->addUsersGame($userGame);
        $appointment->setUpdatedValue();

	    $matchmaking = $appointment->getMatchmaking();
	    /**
	     * on ajoute 30 minutes
	     */
	    if($matchmaking !== null){
		    $dateInterval = new \DateInterval('P0Y0M0DT0H30M0S');
		    $endDay = new \DateTime();
		    $endDay->add($dateInterval);
		    $appointment->setEnd($endDay);
	    }

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);

	    /**
	     * mets à jour l'online du user
	     */
	    $user = $userGame->getUser();
	    $user->setOnlineTimeValue();
	    $em->persist($user);

        $em->flush();

        $json = $appointment->_toJson();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $response->setContent($json);
        return $response;

    }

    /**
     * @todo faire le check sur l'utilisateur, plutot que sur le profil de jeu
     * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
     * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
     */
    public function removeUserGameInAllOtherGameInSameTime($userGame,$appointment){
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('
            SELECT appointment FROM AcmeEsBattleBundle:Appointment appointment
            JOIN appointment.usersGameInQueue usersGameInQueue
            WHERE (appointment.start >= :myStart AND appointment.start <= :myEnd)
            OR (appointment.end >= :myStart AND appointment.end <= :myEnd)
            OR (appointment.start <= :myStart AND appointment.end >= :myEnd)
            AND (usersGameInQueue = :currentUserGame)'
        )->setParameters(array('myStart'=> $appointment->getStart(),'myEnd'=>$appointment->getEnd(),'currentUserGame'=>$userGame));

        $collectionInQueue = $query->getResult();

        /**
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointmentConflict
         */
        foreach($collectionInQueue as $appointmentConflict){
            $appointmentConflict->removeUsersGameInQueue($userGame);

            $em->persist($appointmentConflict);
            $em->flush();
        }


        $query = $em->createQuery('
            SELECT appointment FROM AcmeEsBattleBundle:Appointment appointment
            JOIN appointment.usersGame usersGame
            WHERE (appointment.start >= :myStart AND appointment.start <= :myEnd)
            OR (appointment.end >= :myStart AND appointment.end <= :myEnd)
            OR (appointment.start <= :myStart AND appointment.end >= :myEnd)
            AND (usersGame = :currentUserGame)'
        )->setParameters(array('myStart'=> $appointment->getStart(),'myEnd'=>$appointment->getEnd(),'currentUserGame'=>$userGame));


        $collection = $query->getResult();

        /**
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointmentConflict
         */
        foreach($collection as $appointmentConflict){
            $userToRemove = $userGame->getUser();
            $appointmentConflict->removeUsersGame($userGame);

            $leader = $appointmentConflict->getLeader();
            if($userToRemove === $leader){
                $hasNewLeader = $this->setNewLeader($appointmentConflict);

                if($hasNewLeader === false){
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($appointmentConflict);
                    $em->flush();
                    continue;
                }
            }


            $em->persist($appointmentConflict);
            $em->flush();
        }

    }

    /**
     * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
     */
    public function setNewLeader($appointment){
        $hasNewLeader = false;
        $leader = $appointment->getLeader();
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
            $aUsersGame = $appointment->getUsersGameInQueue();
            foreach($aUsersGame as $userGameCanBeLeader){
                $userGameCanBeLeaderAccount = $userGameCanBeLeader->getUser();
                if($userGameCanBeLeaderAccount !== $leader){
                    $appointment->setLeader($userGameCanBeLeaderAccount);
                    $appointment->removeUsersGameInQueue($userGameCanBeLeader);
                    $appointment->addUsersGame($userGameCanBeLeader);
                    $hasNewLeader = true;
                    break;
                }
            }
        }

        return $hasNewLeader;
    }

    public function inviteAction($userId,$rdvId,$username,$apikey){

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(array('username'=>$username,'apikey'=>$apikey));

        if($user === null){
            $response->setStatusCode(401);
            return $response;
        }

        /**
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
         */
        $appointment = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Appointment')
            ->findOneBy(array('id'=>$rdvId));

        if($appointment === null){
            $response->setStatusCode(401);
            return $response;
        }



        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('
            SELECT account, usergames FROM AcmeEsBattleBundle:User account
            JOIN account.usergames usergames
            WHERE account.id = :userId'
        )->setParameters(array('userId'=> $userId));


        $collection = $query->getResult();
        if($collection === null){
            $response->setStatusCode(401);
            return $response;
        }
        /**
         * @var \Acme\EsBattleBundle\Entity\User $userToInvite
         */
        $userToInvite = $collection[0];

        $aUsergames = $userToInvite->getUsergames();

        $maxLevel = 0;
        $userGameToInvite = null;
        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $usergame
         */
        foreach($aUsergames as $usergame){
            $currentLevel = $usergame->getData2();
            if($currentLevel > $maxLevel){
                $maxLevel = $currentLevel;
                $userGameToInvite = $usergame;
            }
        }

        $appointment->addUsersGameInvite($userGameToInvite);
        $em->flush();


        $notification = new Notification();
        $notification->setCode($notification::NEW_INVITATION);
        $notification->setDestinataire($userToInvite);
        $notification->setExpediteur($user);
        $notification->setAppointment($appointment);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();

        $url = 'http://www.esbattle.com/fr/party/waiting/'.$appointment->getId();


        $message = \Swift_Message::newInstance()
            ->setContentType('text/html')
            ->setSubject('Welcome to Esbattle.com')
            ->setFrom('contact.esbattle@gmail.com')
            ->setTo($userToInvite->getEmail())
            ->setBody($this->renderView('AcmeEsBattleBundle:Mail:invite-party.html.twig',array('username' => $userToInvite->getUsername(),'from'=>$user->getUsername(),'url'=>$url)));
        $this->get('mailer')->send($message);

        return $response;

    }
}
