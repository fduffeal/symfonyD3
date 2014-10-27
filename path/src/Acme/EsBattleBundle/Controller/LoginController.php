<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    public function indexAction(Request $request)
    {

        $email = $request->query->get('email');
        $password = $request->query->get('password');

        var_dump($_REQUEST);die();

        /*$user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findByLogin($login);*/

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('email' => $email)
            );

	    if($user->isPasswordOk($password)){

            $user->setApikey($user->createApiKey());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $aUser = $user->_toArray();

            $encoders = array(new XmlEncoder(), new JsonEncoder());
            $normalizers = array(new GetSetMethodNormalizer());

            $serializer = new Serializer($normalizers, $encoders);

            $json = $serializer->serialize($aUser, 'json');

		    return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	    } else {
		    return new Response(null, 404, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	    }

    }

	public function registerAction($email,$password,$username)
	{

		$user = new User;

		$user->setEmail($email);
		$user->setUsername($username);
		$user->setPassword($user->makePassword($password));


		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

        $aUser = $user->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $json = $serializer->serialize($aUser, 'json');


        return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	}
}
