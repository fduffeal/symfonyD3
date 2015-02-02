<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class StatsController extends Controller
{
	/**
	 * par jour / total
	 * nombre de parties créé
	 * nombre d'annonce créé (les vrais)
	 * nombre de users connectés
	 * nombre de nouvel inscrit
	 * nombre de message posté
	 */

	/**
	 * temps réels
	 * le nombre de users connectés
	 */

	public function indexAction()
	{
		$response = new Response();

		// Idem mais uniquement pour les caches partagés

		echo '<br/>nb user total : '.$this->_getNbUser();
		echo '<br/>nb user new today : '.$this->_getNbUserSince('now','+1 day');
		echo '<br/>nb user new -1 day : '.$this->_getNbUserSince('-1 day','now');
		echo '<br/>nb user connected today : '.$this->_getNbConnectes('now','+1 day');
		echo '<br/>nb user connected -1 day : '.$this->_getNbConnectes('-1 day','now');
		echo '<br/>nb party : '.$this->_getNbParties(null,'+1 day');
		echo '<br/>nb party today: '.$this->_getNbParties('now','+1 day');
		echo '<br/>nb party -1 day: '.$this->_getNbParties('-1 day','now');
		echo '<br/>nb annonces : '.$this->_getNbAnnonces(null,'+1 day');
		echo '<br/>nb annonces today : '.$this->_getNbAnnonces('now','+1 day');
		echo '<br/>nb annonces -1 day : '.$this->_getNbAnnonces('-1 day','now');

		return $response;
	}

	/*
	 * nombre d'utilisateurs
	 */
	public function _getNbUserSince($since,$to)
	{

		$since_date = date('Y-m-d', strtotime($since, time()));
		$to_date = date('Y-m-d', strtotime($to, time()));

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT count(user.id) as nbUserCreated
            FROM AcmeEsBattleBundle:User user
            WHERE user.created >= :since and user.created <= :to'
		)
			->setParameter('since', $since_date)
			->setParameter('to',$to_date);

		$data = $query->getResult();

		return $data[0]['nbUserCreated'];
	}

	/*
	 * nombre de user
	 */
	public function _getNbUser()
	{
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT count(user.id) as nbUserCreated
            FROM AcmeEsBattleBundle:User user'
		);

		$data = $query->getResult();
		return $data[0]['nbUserCreated'];
	}

	/*
	 * nombre de connectés aujourd'hui
	 */
	private function _getNbConnectes($since,$to)
	{

		$since_date = date('Y-m-d', strtotime($since, time()));
		$to_date = date('Y-m-d', strtotime($to, time()));

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT count(user.id) as nbUserConnected
            FROM AcmeEsBattleBundle:User user
            WHERE user.onlineTime >= :since and user.onlineTime <= :to'
		)
			->setParameter('since', $since_date)
			->setParameter('to', $to_date);

		$data = $query->getResult();
		return $data[0]['nbUserConnected'];
	}


	/*
	 * nombre de parties créés
	 */
	private function _getNbParties($since,$to)
	{

		$stop_date = date('Y-m-d', strtotime($since, time()));
		$to_date = date('Y-m-d', strtotime($to, time()));

		$em = $this->getDoctrine()->getManager();

		$dql = "select count(partie.id) as nbParty from AcmeEsBattleBundle:Appointment partie WHERE partie.start >= :now and partie.start <= :to ";

		$query = $em->createQuery($dql)->setParameter('now', $stop_date)->setParameter('to', $to_date);



		$data = $query->getResult();

		return $data[0]['nbParty'];
	}



	/*
	 * nombre d'annonces créés
	 */
	private function _getNbAnnonces($since,$to)
	{
		$stop_date = date('Y-m-d', strtotime($since, time()));
		$to_date = date('Y-m-d', strtotime($to, time()));

		$em = $this->getDoctrine()->getManager();

		$dql = "select count(annonce.id) as nbAnnonces from AcmeEsBattleBundle:Annonce annonce
        JOIN annonce.author author
        JOIN author.user user
		WHERE annonce.created >= :now and annonce.created <= :to";

		$query = $em->createQuery($dql)->setParameter('now', $stop_date)->setParameter('to', $to_date);

		$data = $query->getResult();

		return $data[0]['nbAnnonces'];
	}

	public function classByPlateform()
	{

		$em = $this->getDoctrine()->getManager();

		$dql = "select plateform.nom,usergame.data_1,count(usergame.id) as nb from AcmeEsBattleBundle:Plateform plateform
                JOIN plateform.usergames usergame
                JOIN usergame.user user
                group by plateform.id, usergame.data_1";

		$query = $em->createQuery($dql);

		$data = $query->getResult();

		return $data;
	}
}
