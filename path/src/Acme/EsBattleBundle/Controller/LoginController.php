<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function indexAction($email,$password)
    {

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

            $aUser = array(
                'username' => $user->getUsername(),
                'token' => $user->getApikey()
            );

            $json = json_encode($aUser);

		    return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	    } else {
		    return new Response(null, 404, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	    }

    }

	/**
	 * @param $email
	 * @param $password
	 * @param $username
	 * @return Response
	 *
	 * @todo checker email et username
	 */
	public function registerAction($email,$password,$username)
	{

		$user = new User;

		$user->setEmail($email);
		$user->setUsername($username);
		$user->setPassword($user->makePassword($password));

		$user->setApikey($user->createApiKey());

		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

        $aUser = array(
            'username' => $user->getUsername(),
            'token' => $user->getApikey()
        );

        $json = json_encode($aUser);

        return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	}
}
