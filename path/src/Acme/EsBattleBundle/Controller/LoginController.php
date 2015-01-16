<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserGame;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{

    private function _updateDestinyCharacter($user){
        $bungie = $this->get('acme_es_battle.bungie');
        $userGameCollection = $user->getUsergames();
        $aGamerTag = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
         */
        foreach($userGameCollection as $userGame){
            $gamerTag = $userGame->getGameUsername();
            $gamerTagLower = strtolower($gamerTag);
            if(in_array($gamerTagLower,$aGamerTag)){
                continue;
            }
            $aGamerTag[] = $gamerTagLower;

            /**
             * @var \Acme\EsBattleBundle\Entity\Plateform $plateform
             */
            $plateform = $userGame->getPlateform();
            $game = $userGame->getGame();

            $characters = $bungie->getCharacters($plateform->getBungiePlateformId(),$gamerTag);

            if($characters !== null){
                foreach($characters as $key => $character){
                    $userGame = $bungie->saveGameUserInfo($character,$user,$plateform,$game);
                }
            }
        }
        return $user;
    }

    public function indexAction($username,$password)
    {

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username)
            );

        $response = new Response();

        if($user === null){
            $response->setStatusCode(401);
            return $response;
        }

	    if($user->isPasswordOk($password)){

            $user->setApikey($user->createApiKey());

            $this->_updateDestinyCharacter($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $json = $user->_toJsonPrivate();
		    $response->setContent($json);
	    } else {
		    $response->setStatusCode(401);
            $content = array('msg'=> 'connection_refused');
            $response->setContent(json_encode($content));

	    }

        return $response;

    }

    public function forgetAuthAction($username,$token){

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'forgetKey'=>$token)
            );

        $response = new Response();

        if($user){

            $user->setForgetKey(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $json = $user->_toJsonPrivate();
            $response->setContent($json);
        } else {
            $response->setStatusCode(401);
            $content = array('msg'=> 'token_expired');
            $response->setContent(json_encode($content));

        }

        return $response;
    }

    public function setOnlineAction($username,$token){

        $response = new Response();

        if($username === 'null' && $token === 'null'){
            $response->setStatusCode(403);
        } else {

            /**
             * @var \Acme\EsBattleBundle\Entity\User $user
             */
            $user = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:User')
                ->findOneBy(
                    array('username' => $username, 'apikey' => $token)
                );

            if ($user) {
                $em = $this->getDoctrine()->getManager();
                $user->setOnlineTimeValue();
                $em->persist($user);
                $em->flush();

                $json = $user->_toJsonPrivate();
                $response->setContent($json);
            } else {
                $response->setStatusCode(404);
            }
        }

        return $response;
    }

	public function refreshAction($username,$token)
	{

        $response = new Response();

		$user = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:User')
			->findOneBy(
				array('username' => $username,'apikey'=>$token)
			);

		if($user){
            $json = $user->_toJsonPrivate();
			$response->setContent($json);
		} else {
			$response->setStatusCode(401);
		}

        return $response;
	}

	/**
	 * @param $email
	 * @param $password
	 * @param $username
	 * @param $plateformId
	 * @param $gamerTag
	 * @return Response
	 *
	 */
	public function registerAction($email,$password,$username,$plateformId,$gamerTag)
	{

        $response = new Response();

        $bungie = $this->get('acme_es_battle.bungie');

        $error = array();
        $userWithSameUserName = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(array('username'=>$username));

        if($userWithSameUserName !== null){
            $error[] = 'username_already_taken';
        }

        $userWithSameEmail = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(array('email'=>$email));

        if($userWithSameEmail !== null){
            $error[] = 'email_already_taken';
        }


        /**
         * @var \Acme\EsBattleBundle\Entity\Plateform $plaform
         */
        $plaform = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Plateform')
            ->findOneBy(
                array('id' => $plateformId)
            );

        /**
         * @var \Acme\EsBattleBundle\Entity\Game $game
         */
        $game = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Game')
            ->findOneBy(
                array('id' => $bungie->getDestinyGameId())
            );


		$user = new User;

		$user->setEmail($email);
		$user->setUsername($username);
		$user->setPassword($user->makePassword($password));

		$user->setApikey($user->createApiKey());

        $characters = $bungie->getCharacters($plaform->getBungiePlateformId(),$gamerTag);
        if($characters === null){
            $error[] = 'gamertag_not_found';
        }

        if(sizeof($error) !== 0){
            $result = array('aError' => $error);
            $json = json_encode($result);

            $response->setContent($json);
            $response->setStatusCode(403);
            return $response;
        }

		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

        foreach($characters as $key => $character){
            $userGame = $bungie->saveGameUserInfo($character,$user,$plaform,$game);
            $user->addUsergame($userGame);
        }

		$message = \Swift_Message::newInstance()
			->setContentType('text/html')
			->setSubject('Welcome to Esbattle.com')
			->setFrom('contact.esbattle@gmail.com')
			->setTo($email)
			->setBody($this->renderView('AcmeEsBattleBundle:Mail:register.html.twig',array('username' => $username)));
		$this->get('mailer')->send($message);
        
        $json = $user->_toJsonPrivate();

		$response->setContent($json);
		return $response;
	}

    /**
     * @param $email
     * @param $password
     * @param $username
     * @return Response
     *
     */
    public function updatePasswordAction($password,$username,$apikey)
    {

        $response = new Response();

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey'=>$apikey)
            );

        if($user === null){
            $response->setStatusCode(401);
            return $response;
        }

        $user->setPassword($user->makePassword($password));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $result = $user->_toJsonPrivate();

        $response = new Response();
        $response->setContent($result);
        return $response;
    }

	public function forgetPasswordAction($email){

		$user = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:User')
			->findOneBy(
				array('email' => $email)
			);

        $stop_date = strtotime('-1 hour', time());

        if($user->getForgetTime() && $user->getForgetTime()->getTimestamp() > $stop_date){
            $response = new Response();
            $response->setStatusCode(403);
            $content = array('msg'=> 'mail_already_send');
            $response->setContent(json_encode($content));
            return $response;
        }


        $user->setForgetKey($user->createApiKey());

        $forgetDay = new \DateTime();
        $user->setForgetTime($forgetDay);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $username = $user->getUsername();
        $forgetKey = $user->getForgetKey();

		$message = \Swift_Message::newInstance()
			->setContentType('text/html')
			->setSubject('Esbattle.com password lost ?')
			->setFrom('contact.esbattle@gmail.com')
			->setTo($email)
			->setBody($this->renderView('AcmeEsBattleBundle:Mail:forgetPassword.html.twig',array('username' => $username,'forgetKey'=>$forgetKey)));
		$this->get('mailer')->send($message);

        return new Response();
	}

    public function updateUserGameAction($plateformId,$gameId,$profilId,$profilName,$gameUsername,$data1,$data2,$data3,$data4,$username,$apikey){

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey'=>$apikey)
            );

        if($user === null){
	        $response = new Response();
	        $response->setStatusCode(401);
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
                array('id' => $profilId,'user' => $user)
            );

        if($userGame === null){

            $response = new Response();
            $response->setStatusCode(401);
            return $response;


        }

        $userGame->setUser($user);
        $userGame->setPlateform($plateform);
        $userGame->setGame($game);
        $userGame->setGameProfilName($profilName);
        $userGame->setGameUsername($gameUsername);
        $userGame->setData1($data1);
        $userGame->setData2($data2);
        $userGame->setData3($data3);
        $userGame->setData4($data4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($userGame);
        $em->flush();

        $json = $user->_toJsonPrivate();

	    $response = new Response();
	    $response->setContent($json);
	    return $response;

    }

    public function createUserGameAction($plateformId,$gameId,$profilName,$gameUsername,$data1,$data2,$data3,$data4,$username,$apikey){

        $response = new Response();

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey'=>$apikey)
            );

        if($user === null){
            $response->setStatusCode(401);
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

        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
         */
        $userGame = new UserGame();
        $userGame->setUser($user);
        $userGame->setPlateform($plateform);
        $userGame->setGame($game);
        $userGame->setGameProfilName($profilName);
        $userGame->setGameUsername($gameUsername);
        $userGame->setData1($data1);
        $userGame->setData2($data2);
        $userGame->setData3($data3);
        $userGame->setData4($data4);

        $em = $this->getDoctrine()->getManager();
        $em->persist($userGame);
        $em->flush();

        $json = $user->_toJsonPrivate();

        $response->setContent($json);
        return $response;

    }

    public function getUsersAction(){

        $response = new Response();


        $stop_date = date('Y-m-d H:i:s', strtotime('-1 day', time()));

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT user
            FROM AcmeEsBattleBundle:User user
            WHERE user.onlineTime > :now'
        )->setParameter('now', $stop_date);

        $collection = $query->getResult();


        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        foreach($collection as $user){
            $aResult[] = $user->_toArray();
        }

        $json = json_encode($aResult);



        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(30);
        $response->setSharedMaxAge(30);
        $response->setContent($json);

        return $response;
    }
}
