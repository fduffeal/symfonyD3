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

//	    $user = $this->container->get('security.context')->getToken()->getUser();
//	    if (!is_object($user) || !$user instanceof UserInterface) {
//		    throw new AccessDeniedException('This user does not have access to this section.');
//	    }


        $collection = $this->getDoctrine()
		    ->getRepository('AcmeEsBattleBundle:Appointment')
		    ->findAll();


        /*$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new
        JsonEncoder()));
        $collection = $serializer->serialize($collection, 'json');*/

        $aResult = [];
        foreach($collection as $appointment){
            $aResult[] = $appointment->_toArray();
        }

	    /*$encoders = array(new XmlEncoder(), new JsonEncoder());
	    $normalizers = array(new GetSetMethodNormalizer());

	    $serializer = new Serializer($normalizers, $encoders);

	    $json = $serializer->serialize($aResult, 'json'); // Output: {"name":"foo","age":99}*/

        $json = json_encode($aResult);


//	    $result = json_encode($result);
//	    var_dump($result);
//	    $response = new \Response(json_encode($result));
//	    $response->headers->set('Content-Type', 'application/json');
//
//	    return $response;

//	    var_dump($result);



//        var_dump($json);die();
// create a simple Response with a 200 status code (the default)

// create a JSON-response with a 200 status code
//	    $response = new Response(json_encode($result));
//	    $response->headers->set('Content-Type', 'application/json');


//	    $response = new JsonResponse();
//	    $response->setData($result);

//	    $json = $result;
//	    var_dump($json);
	    return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

//	    return $response;
//	    return $this->render('AcmeEsBattleBundle:Default:json.html.twig', array('json' => $result));
    }

	public function createAction($plateform,$game,$tags,$description,$start,$duree,$nbParticipant,$username,$token)
	{

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $token)
            );

		$startDay = new \DateTime();
		$startDay->setTimestamp($start);
		$nbParticipant = intval($nbParticipant);

		$appointment = new Appointment();
		$appointment->setDescription($description);
		$appointment->setStart($startDay);
		$appointment->setDuree($duree);
		$appointment->setNbParticipant($nbParticipant);
        $appointment->setLeader($user);
        $appointment->addUser($user);


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


        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

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

        $response = array(
            'tags' => $aTags
        );
        $json = json_encode($response);
        return new Response($json, 200, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));

    }
}
