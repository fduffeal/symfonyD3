<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\Document;
use Acme\EsBattleBundle\Entity\Topic;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Acme\EsBattleBundle\Entity\UserGame;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;
use Acme\EsBattleBundle\Entity\Annonce as Annonce;
use Acme\EsBattleBundle\Entity\Tag as Tag;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;

class AdminController extends Controller
{
    public function checkUserGameAction(){

        $response = new Response();

        $bungie = $this->get('acme_es_battle.bungie');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT user
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames'
        );

        $collection = $query->getResult();

        $aUser = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        foreach($collection as $user){
            $aUser[] = $user->_toArray();

            $aUserGames = $user->getUsergames();

            $aUserNames = [];
            /**
             * @var \Acme\EsBattleBundle\Entity\UserGame $usergames
             */
            foreach($aUserGames as $usergames){
                $gamerTag = $usergames->getGameUsername();
                $plaform = $usergames->getPlateform();
                $game = $usergames->getGame();
                if(in_array($gamerTag,$aUserNames)){
                    continue;
                }

                $aUserNames[] = $gamerTag;

                $characters = $bungie->getCharacters($plaform->getBungiePlateformId(),$gamerTag);
                if($characters !== null){
                    foreach($characters as $key => $character){

                        $alreadyAdded = false;
                        foreach($aUserGames as $usergames){
                            if($character['characterId'] === $usergames->getExtId()){
                                echo $character['characterId']." already added<br/>";
                                $alreadyAdded = true;
                            }
                        }

                        if($alreadyAdded === true){
                            continue;
                        }

                        $userGame = $bungie->saveGameUserInfo($character,$user,$plaform,$game);
                        echo $character['characterId']." added to".$user->getUsername()."<br/>";
                        $user->addUsergame($userGame);
                    }
                }
            }
        }


        return $response;
    }
    public function removeOldUserGameAction(){
        $response = new Response();

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT appointment
            FROM AcmeEsBattleBundle:Appointment appointment
            JOIN appointment.leader leader
            LEFT JOIN leader.usergames userGameLeader
            LEFT JOIN appointment.usersGame usersGame
            LEFT JOIN appointment.usersGameInQueue usersGameInQueue
            WHERE userGameLeader.ext_id IS NULL
            OR usersGame.ext_id IS NULL
            OR usersGameInQueue.ext_id IS NULL'
        );


        $collection = $query->getResult();

        echo sizeof($collection).'<br/>';


        $em = $this->getDoctrine()->getManager();

        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
         */
        foreach($collection as $appointment){
            //echo $userGame->_toJson();
            $aUsersGame = $appointment->getUsersGame();

            foreach($aUsersGame as $userGameSelected){
                $userGameNew = self::changeUser($appointment,$userGameSelected);
                if($userGameNew !== false){

                    if($userGameNew !== null){
                        $appointment->addUsersGame($userGameNew);
                        echo $userGameNew->getGameProfilName().'remplace '.$userGameSelected->getGameProfilName().'<br/>';
                    }

                    $appointment->removeUsersGame($userGameSelected);
                    $em->remove($userGameSelected);
                    echo 'supprime '.$userGameSelected->getGameProfilName().'<br/>';

                }
            }

            $aUsersGame = $appointment->getUsersGameInQueue();
            foreach($aUsersGame as $userGameSelected){
                $userGameNew = self::changeUser($appointment,$userGameSelected);
                if($userGameNew !== false){

                    if($userGameNew !== null){
                        $appointment->addUsersGameInQueue($userGameNew);
                        echo $userGameNew->getGameProfilName().'remplace '.$userGameSelected->getGameProfilName().'<br/>';
                    }

                    $appointment->removeUsersGameInQueue($userGameSelected);
                    $em->remove($userGameSelected);
                    echo 'supprime '.$userGameSelected->getGameProfilName().'<br/>';
                }
            }

            if(sizeof($appointment->getUsersGameInQueue()) === 0 && sizeof($appointment->getUsersGame()) === 0 ){
                $em->remove($appointment);

                echo 'supprime rdv '.$appointment->getId().'<br/>';
            } else {
                $hasLeader = false;
                $lastUserGame = null;
                foreach($appointment->getUsersGame() as $userGame){
                    if($userGame->getUser()->getId() === $appointment->getLeader()->getId()){
                        $hasLeader = true;
                    }
                    $lastUserGame = $userGame;
                }
                if($hasLeader === false){
                    $appointment->setLeader($lastUserGame->getUser());
                    echo 'changement de leader '.$appointment->getId().'<br/>';
                }

                $em->persist($appointment);
                echo 'save rdv '.$appointment->getId().'<br/>';
            }



        }

        $em->flush();

        return $response;

    }


    public function deleteOldUserGameAction()
    {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT usergame
            FROM AcmeEsBattleBundle:UserGame usergame
            JOIN usergame.user user
            WHERE  usergame.ext_id IS NULL'
        );

        $collection = $query->getResult();

        foreach($collection as $usergame){
            $em->remove($usergame);
            echo 'remove '.$usergame->getId();
        }

        $em->flush();

        return $response;
    }

    /**
     * @var \Acme\EsBattleBundle\Entity\User $user
     * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
     *
     * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
     */
    public function changeUser($appointment,$userGame){

        if($userGame->getExtId() === null){
            $user = $userGame->getUser();

            $aUserGame = $user->getUsergames();
            /**
             * @var \Acme\EsBattleBundle\Entity\UserGame $userGameSelected
             */
            foreach($aUserGame as $userGameSelected){
                if($userGameSelected->getExtId() !== null){
                    return $userGameSelected;
                }
            }

            return null;
        }

        return false;
    }

    public function copyJeuxVideoAction($url){
        $response = new Response();

        $em = $this->getDoctrine()->getManager();

        /**
         * Acme\EsBattleBundle\JeuxVideo $jeuxvideo
         */
        $jeuxvideo = $this->get('acme_es_battle.jeuxvideo');
        $bungie = $this->get('acme_es_battle.bungie');

        $pagePost = $jeuxvideo->getPage($url);

        $aPost =[];
        foreach($pagePost as $post) {
            if ($post === null) {
                continue;
            }
            $aPost[] = $post;
        }
//        echo 'NB POST :'. sizeof($aPost).'<br/>';

        $plateformId = $jeuxvideo->plateform;//PS4
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

//        var_dump($pagePost[0]);die();

        $aAnnonce = [];
        foreach($aPost as $post){
            if($post === null){
                continue;
            }
//            var_dump($post->gamerTag);die();
            $displayName = $post->gamerTag;
            $membershipType = $plaform->getBungiePlateformId();

            $response = $this->forward('AcmeEsBattleBundle:User:getDestinyUsersGame', array(
                'membershipType'  => $membershipType,
                'displayName'  => $displayName
            ));

            $account = json_decode($response->getContent());
            if($account === null){
                continue;
            }

//            echo 'ACCOUNT <BR/>';
//            var_dump($account);echo '<BR/><BR/>';




            $characters = $bungie->formatCharacters($account,$displayName);

//            var_dump($characters);
            if(!$characters[0]){
//                echo $displayName.' not found<br/>';
                continue;
            }

            $characters = $bungie->sortCharacters($characters);

            $userGameToSave = null;

            foreach($characters as $character){
                if($character['class'] ===  $post->class){
//                    echo 'i m '. $post->class.'<br/>';
                    $userGameToSave = $character;
                    break;
                }
            }

            if($userGameToSave === null){
                $userGameToSave = $characters[0];
            }

            $userGame = $bungie->saveGameUserInfo($userGameToSave,null,$plaform,$game);


            /**
             * @var \Acme\EsBattleBundle\Entity\Annonce $annonce
             */
            $annonce = new Annonce();
            $annonce->setDescription($post->message);
            $annonce->setAuthor($userGame);
            $annonce->setPlateform($plaform);
            $annonce->setGame($game);


            $aTags = preg_split("/[\s,]+/",$post->tags);



            foreach($aTags as $key){
                $selectedTag = $this->getDoctrine()
                    ->getRepository('AcmeEsBattleBundle:Tag')
                    ->findOneBy(array('nom' => $key));

                $key = trim($key);

                if($selectedTag === null && $key !== ""){
                    $selectedTag = new Tag();
                    $selectedTag->setNom($key);
                    $selectedTag->setPoids(0);
                    $em->persist($selectedTag);
                }

                if($selectedTag !== null){
                    $annonce->addTag($selectedTag);
                }
            }

            $em->persist($annonce);

            $aHeure = explode(':',$post->heure);
            $date = new \DateTime();
            $date->setTime($aHeure[0],$aHeure[1],$aHeure[2]);
            $annonce->setCreated($date);

            $aAnnonce[] = $annonce->_toArray();
        }

        $em->flush();

        $response->setContent(json_encode($aAnnonce));
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(86400);
        $response->setSharedMaxAge(86400);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

	/**
	 * @Template()
	 */
	public function uploadAction(Request $request)
	{
		$session = $request->getSession();
		if(!$session->get('user')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

		$document = new Document();
		$form = $this->createFormBuilder($document)
			->add('name')
			->add('file')
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();

			$em->persist($document);
			$em->flush();

			$imgUrl = 'http://'.$_SERVER['SERVER_NAME'].'/'.$document->getWebPath();

			return $this->redirect($imgUrl);
		}

		return array('form' => $form->createView());
	}

	public function bibliothequeAction()
	{

		$session = new Session();

		if(!$session->get('user')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findAll();


		return $this->render('AcmeEsBattleBundle:Admin:bibliotheque.html.twig', array(
			'documents' => $collectionDocument
		));
	}

	public function topicAction($id,Request $request)
	{
		$session = new Session();

		if(!$session->get('user')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}
		/**
		 * @var \Acme\EsBattleBundle\Entity\Topic $topic
		 */
		$topic = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Topic')
			->find($id);

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findAll();

		$topicStatus = [];
		$topicStatus[] = Topic::STATUS_NEWS;
		$topicStatus[] = Topic::STATUS_NORMAL;
		$topicStatus[] = Topic::STATUS_POSTIT;
		$topicStatus[] = Topic::STATUS_HIGH;


		$newStatus = $request->get('status');
		$newDocument = $request->get('document');

		if ($newStatus !== null) {
			$em = $this->getDoctrine()->getManager();
			$topic->setStatus($newStatus);
			$newDocumentEntity = null;

			if($newDocument !== null){
				$newDocumentEntity = $this->getDoctrine()
					->getRepository('AcmeEsBattleBundle:Document')
					->find($newDocument);
			}

			$topic->setDocument($newDocumentEntity);
			$em->flush();
		}


		return $this->render('AcmeEsBattleBundle:Admin:topic.html.twig', array(
			'documents' => $collectionDocument,
			'topic' => $topic,
			'aStatus' => $topicStatus
		));
	}

	/**
	 * @Template()
	 */
	public function loginAction(Request $request)
	{

		$user = new User();
		$form = $this->createFormBuilder($user)
			->add('username')
			->add('password')
			->getForm();

		$form->handleRequest($request);

		$logged = false;

		if ($form->isValid()) {


			$username = $user->getUsername();
			$password = $user->getPassword();

			/**
			 * @var \Acme\EsBattleBundle\Entity\User $user
			 */
			$user = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:User')
				->findOneBy(
					array('username' => $username)
				);


			if($user !== null && $user->isPasswordOk($password) && $user->isModo()){
				$session = new Session();
				$session->set('user',$user->_toArrayShort());
				$logged = true;
			}

		}

		return array('form' => $form->createView(),'logged' => $logged);
	}
}
