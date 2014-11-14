<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserGame;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function indexAction($username,$password)
    {

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username)
            );

	    if($user->isPasswordOk($password)){

            $user->setApikey($user->createApiKey());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $userGameCollection = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:UserGame')
                ->findBy(
                    array('user' => $user)
                );

            foreach($userGameCollection as $key => $userGame){
                $userGameCollection[$key] = $userGame->_toArray();
            }


            $aUser = array(
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'token' => $user->getApikey(),
                'userGame'=> $userGameCollection
            );

            $json = json_encode($aUser);

		    $response = new Response();
		    $response->setContent($json);
		    return $response;

	    } else {
		    $response = new Response();
		    $response->setStatusCode(404);
		    return $response;
	    }

    }


	public function refreshAction($username,$token)
	{

		$user = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:User')
			->findOneBy(
				array('username' => $username,'apikey'=>$token)
			);

		if($user){
			$userGameCollection = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:UserGame')
				->findBy(
					array('user' => $user)
				);

			foreach($userGameCollection as $key => $userGame){
				$userGameCollection[$key] = $userGame->_toArray();
			}


			$aUser = array(
				'username' => $user->getUsername(),
				'email' => $user->getEmail(),
				'token' => $user->getApikey(),
				'userGame'=> $userGameCollection
			);

			$json = json_encode($aUser);

			$response = new Response();
			$response->setContent($json);
			return $response;

		} else {
			$response = new Response();
			$response->setStatusCode(404);
			return $response;
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

		$message = \Swift_Message::newInstance()
			->setContentType('text/html')
			->setSubject('Welcome to Esbattle.com')
			->setFrom('contact.esbattle@gmail.com')
			->setTo($email)
			->setBody($this->renderView('AcmeEsBattleBundle:Mail:register.html.twig',array('username' => $username)));
		$this->get('mailer')->send($message);

        $aUser = array(
            'username' => $user->getUsername(),
            'token' => $user->getApikey()
        );

        $json = json_encode($aUser);

		$response = new Response();
		$response->setContent($json);
		return $response;
	}

	public function forgetPasswordAction($email){

		$user = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:User')
			->findOneBy(
				array('email' => $email)
			);

		$username = $user->getUsername();

		$message = \Swift_Message::newInstance()
			->setContentType('text/html')
			->setSubject('Welcome to Esbattle.com')
			->setFrom('contact.esbattle@gmail.com')
			->setTo($email)
			->setBody($this->renderView('AcmeEsBattleBundle:Mail:forgetPassword.html.twig',array('username' => $username)));
		$this->get('mailer')->send($message);

		return new Response(null, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	}

    public function updateUserGameAction($plateformId,$gameId,$profilName,$gameUsername,$data1,$data2,$data3,$data4,$username,$apikey){
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username)
            );

        if($user === null || $user->isPasswordOk($apikey)){
	        $response = new Response();
	        $response->setStatusCode(501);
	        return $response;
        }

        $plateform = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Plateform')
            ->findOneBy(
                array('id' => $plateformId)
            );

        $game = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Game')
            ->findOneBy(
                array('id' => $gameId)
            );


        $userGame = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findOneBy(
                array('user' => $user,'plateform' => $plateform,'game' => $game)
            );

        if($userGame === null){
            $userGame = new UserGame();
            $userGame->setUser($user);
            $userGame->setPlateform($plateform);
            $userGame->setGame($game);
        }

        $userGame->setGameProfilName($profilName);
        $userGame->setGameUsername($gameUsername);
        $userGame->setData1($data1);
        $userGame->setData2($data2);
        $userGame->setData3($data3);
        $userGame->setData4($data4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($userGame);
        $em->flush();


        $userGameCollection = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:UserGame')
            ->findBy(
                array('user' => $user)
            );

        foreach($userGameCollection as $key => $userGame){
            $userGameCollection[$key] = $userGame->_toArray();
        }


        $aUser = array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'token' => $user->getApikey(),
            'userGame'=> $userGameCollection
        );

        $json = json_encode($aUser);

	    $response = new Response();
	    $response->setContent($json);
	    return $response;

    }
}
