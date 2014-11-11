<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;

use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;



use Acme\EsBattleBundle\Entity\User;
use Acme\EsBattleBundle\Entity\Appointment;

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

	    return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
    }

	public function createAction($plateform,$game,$tags,$description,$start,$duree,$nbParticipant,$userGameId,$username,$token)
	{

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $token)
            );

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

        foreach($aTags as $key){
            $selectedTag = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:Tag')
                ->findOneBy(array('nom' => $key));

            $appointment->addTag($selectedTag);
        }

		$em = $this->getDoctrine()->getManager();
		$em->persist($appointment);
		$em->flush();

        $json = $appointment->_toJson();


        return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

	}

	public function getRdvByIdAction($rdvId){
		$appointment = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Appointment')
			->findOneBy(array('id'=>$rdvId));

		$json = $appointment->_toJson();
		return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	}

    public function joinRdvAction($rdvId,$userGameId,$username,$apikey){
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(array('username'=>$username,'apikey'=>$apikey));

        if($user === null){
            return new Response(null, 501, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
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

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);
        $em->flush();

        $json = $appointment->_toJson();
        return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

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

            $em = $this->getDoctrine()->getManager();
            $em->persist($appointment);
            $em->flush();

            $json = $appointment->_toJson();
            return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));


        }else{
            return new Response(null, 501, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($appointment);
            $em->flush();

            $json = $appointment->_toJson();
            return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

        }else {
            return new Response(null, 501, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

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
            return new Response(null, 501, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
        }

        $appointment->removeUsersGame($userGame);
        $appointment->removeUsersGameInQueue($userGame);

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);
        $em->flush();

        $json = $appointment->_toJson();

        return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));


    }

    public function getFormInfoAction(){
        $tags = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Tag')
            ->findAll();

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

        $response = array(
            'tags' => $aTags,
            'plateforms' => $aPlateforms,
            'games' => $aGame
        );
        $json = json_encode($response);
        return new Response($json, 200, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

    }
}
