<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Config\Definition\Exception\Exception;

class NotificationController extends Controller
{

	/**
	 * @return Response
	 */
	public function indexAction($userId){

        $stop_date = date('Y-m-d H:i:s', strtotime('-1 day', time()));
        $aNotification = array();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $response->setPrivate();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(30);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT notifications
            FROM AcmeEsBattleBundle:Notification notifications
            JOIN notifications.destinataire destinataire
            WHERE destinataire.id = :userId ORDER BY notifications.updated DESC'
        )->setParameter('userId',$userId)->setMaxResults(1);

        $result = $query->getResult();

        if(!$result){
            $date = new \DateTime();
            $response->setLastModified($date);
            $response->setContent(json_encode($aNotification));
            return $response;
        }


        /**
         * @var \Acme\EsBattleBundle\Entity\Notification $lastNotif
         */
        $lastNotif = $result[0];
        $response->setLastModified($lastNotif->getUpdated());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT notifications, expediteur, destinataire,appointment,tags
            FROM AcmeEsBattleBundle:Notification notifications
            JOIN notifications.expediteur expediteur
            JOIN notifications.destinataire destinataire
            LEFT JOIN notifications.appointment appointment
            LEFT JOIN appointment.tags tags
            WHERE notifications.created > :stop_date and destinataire.id = :userId and notifications.new = :new ORDER BY notifications.created DESC'
		)->setParameters(array('stop_date'=>$stop_date,'userId'=>$userId,'new'=>true));

		$collection = $query->getResult();

        foreach($collection as $key => $notification){
            $aNotification[$key] = $notification->_toArray();
        }

        $response->setContent(json_encode($aNotification));

		return $response;
	}

    /**
     * @return Response
     */
    public function markAllAsReadAction($userId,$notificationsId){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');


        $aNotificationsId = explode('-',$notificationsId);
        foreach($aNotificationsId as $key => $id){
            $aNotificationsId[$key] = intval($id);
        }

//        var_dump($aNotificationsId);return $response;

        $aNotification = array();


        $response->setPrivate();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(30);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT notifications
            FROM AcmeEsBattleBundle:Notification notifications
            JOIN notifications.destinataire destinataire
            WHERE notifications.id IN (:arrayId) and destinataire.id  = :userId'
        )->setParameter('arrayId',$aNotificationsId)->setParameter('userId',$userId);

        $collection = $query->getResult();

        /**
         * @var \Acme\EsBattleBundle\Entity\Notification $notification
         */
        foreach($collection as $key => $notification){
            $aNotification[$key] = $notification->setNew(false);
        }

        $em->flush();

        $response = $this->forward('AcmeEsBattleBundle:Notification:index', array(
            'userId'  => $userId
        ));

        return $response;
    }
}
