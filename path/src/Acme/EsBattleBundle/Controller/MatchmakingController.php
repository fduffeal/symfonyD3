<?php
/**
 * Created by PhpStorm.
 * User: francis.duffeal
 * Date: 24/11/2014
 * Time: 17:13
 */

namespace Acme\EsBattleBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Acme\EsBattleBundle\Entity\User;
use Acme\EsBattleBundle\Entity\Appointment;
use Acme\EsBattleBundle\Entity\Notification;
use Acme\EsBattleBundle\Entity\Matchmaking;

class MatchmakingController extends Controller
{
	public function indexAction()
	{
		$response = new Response();
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT matchmaking
            FROM AcmeEsBattleBundle:Matchmaking matchmaking
            JOIN matchmaking.tags tags
            JOIN matchmaking.game game
            JOIN matchmaking.plateforms plateforms');

		$collection = $query->getResult();

		$aMatchmaking = array();
		foreach($collection as $matchmaking){
			$aMatchmaking[] = $matchmaking->_toArray();
		}

		$response->setContent(json_encode($aMatchmaking));
		$response->setPublic();
		// définit l'âge max des caches privés ou des caches partagés
		$response->setMaxAge(600);
		$response->setSharedMaxAge(600);

		return $response;
	}

    public function joinAction($matchmakingId,$profilId,$username,$apikey){
        $response = new Response();

        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey'=>$apikey)
            );

        if($user === null){
            $response->setStatusCode(401);
            return $response;
        }


        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
         */
        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(
                array('id' => $profilId)
            );

        if($userGame === null){
            $response->setStatusCode(403);
            return $response;
        }

        $now = date('Y-m-d H:i:s', time());


        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('
            SELECT appointment FROM AcmeEsBattleBundle:Appointment appointment
            JOIN appointment.matchmaking matchmaking
            JOIN appointment.usersGame usersGame
            JOIN appointment.plateform plateform
            WHERE matchmaking.id = :id
            AND appointment.start < :now
            AND appointment.end > :now
            AND plateform.id = :plateformId
            ORDER BY appointment.start ASC'
            )->setParameters(array('id'=> $matchmakingId,'now'=>$now,'plateformId'=>$userGame->getPlateform()->getId()));

        $collection = $query->getResult();

        $sizeOfResult = sizeof($collection);

        //var_dump($collection);die();

        if($sizeOfResult === 0){

            $response = $this->forward('AcmeEsBattleBundle:Matchmaking:create', array(
                'matchmakingId'  => $matchmakingId,
                'profilId'  => $profilId,
                'username'  => $username,
                'apikey' => $apikey,
            ));

            return $response;
        }

        $aMatchmaking = array();
        /**
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
         */
        foreach($collection as $appointment){

            $collectionUsersGame = $appointment->getUsersGame();

            if(sizeof($collectionUsersGame) < $appointment->getNbParticipant()){

                $response = $this->forward('AcmeEsBattleBundle:Rdv:addUserGameInAppointment', array(
                    'userGame'  => $userGame,
                    'appointment'  => $appointment
                ));
                return $response;
            }
        }
    }

    /**
     * @param $matchmakingId
     * @param $profilId
     * @param $username
     * @param $apikey
     * @return Response
     */
	public function createAction($matchmakingId,$profilId,$username,$apikey){
		$response = new Response();

		/**
		 * @var \Acme\EsBattleBundle\Entity\User $user
		 */
		$user = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:User')
			->findOneBy(
				array('username' => $username,'apikey'=>$apikey)
			);

		if($user === null){
			$response->setStatusCode(401);
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\Matchmaking $matchmaking
		 */
		$matchmaking = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Matchmaking')
			->findOneBy(
				array('id' => $matchmakingId)
			);

		if($matchmaking === null){
			$response->setStatusCode(403);
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
		 */
		$userGame = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:UserGame')
			->findOneBy(
				array('id' => $profilId)
			);

		/**
		 * @var \Acme\EsBattleBundle\Entity\Plateform $myPlateform
		 */
		$myPlateform = $userGame->getPlateform();

		/**
		 * @var \Acme\EsBattleBundle\Entity\Game $myGame
		 */
		$myGame = $matchmaking->getGame();

		$nbParticipant = $matchmaking->getNbParticipant();

		$startDay = new \DateTime();
		$duree = $matchmaking->getDuree();
		$dateInterval = new \DateInterval('P0Y0M0DT'.$duree.'H00M0S');
		$endDay = new \DateTime();
		$endDay->add($dateInterval);

		$appointment = new Appointment();
		$appointment->setDescription($matchmaking->getDescription());
		$appointment->setStart($startDay);
		$appointment->setEnd($endDay);
		$appointment->setDuree($duree);
		$appointment->setNbParticipant($nbParticipant);
		$appointment->setLeader($user);
		$appointment->setPlateform($myPlateform);
		$appointment->setGame($myGame);
        $appointment->setMatchmaking($matchmaking);

		$em = $this->getDoctrine()->getManager();

		$tagCollection = $matchmaking->getTags();

		foreach($tagCollection as $key => $tag){
			$appointment->addTag($tag);
		}

		$em->persist($appointment);
		$em->flush();

        $response = $this->forward('AcmeEsBattleBundle:Rdv:addUserGameInAppointment', array(
            'userGame'  => $userGame,
            'appointment'  => $appointment
        ));
        return $response;

	}
}