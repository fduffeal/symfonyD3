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
}