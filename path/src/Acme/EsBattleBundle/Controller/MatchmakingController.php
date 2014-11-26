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

		return $response;
	}

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
		$appointment->addUsersGame($userGame);
		$appointment->setPlateform($myPlateform);
		$appointment->setGame($myGame);
		$appointment->setIsMatchmaking(true);


		$em = $this->getDoctrine()->getManager();

		$tagCollection = $matchmaking->getTags();

		foreach($tagCollection as $key => $tag){
			$appointment->addTag($tag);
		}

		$em->persist($appointment);
		$em->flush();

		$json = $appointment->_toJson();


		$response = new Response();
		$response->setContent($json);
		return $response;
	}
}