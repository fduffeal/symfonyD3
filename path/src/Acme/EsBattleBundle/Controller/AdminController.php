<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\Document;
use Acme\EsBattleBundle\Entity\Message;
use Acme\EsBattleBundle\Entity\Partenaire;
use Acme\EsBattleBundle\Entity\Topic;
use Acme\EsBattleBundle\Entity\Video;
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
		if(!$session->get('modo') && !$session->get('redacteur')){
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

		if(!$session->get('modo') && !$session->get('redacteur')){
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

		if(!$session->get('modo')){
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


		$topicStatus = [];
		$topicStatus[] = Topic::STATUS_NEWS;
		$topicStatus[] = Topic::STATUS_NORMAL;
		$topicStatus[] = Topic::STATUS_POSTIT;
		$topicStatus[] = Topic::STATUS_HIGH;


		$newStatus = $request->get('status');
		$visible = $request->get('visible');

		if ($newStatus !== null) {
			$em = $this->getDoctrine()->getManager();
			$topic->setStatus($newStatus);

            /**
             * @var \Acme\EsBattleBundle\Entity\Message $message
             */
            $message = $topic->getMessages()->first();

            if($visible === "1" && $message !== null){
                $topic->setVisible(true);
                $message->setVisible(true);
            } else {
                $topic->setVisible(false);
                if($message !== null){
                    $message->setVisible(false);
                }
            }

			$em->flush();
		}


		return $this->render('AcmeEsBattleBundle:Admin:topic.html.twig', array(
			'topic' => $topic,
			'aStatus' => $topicStatus
		));
	}

	/**
	 * @Template()
	 */
	public function loginAction(Request $request)
	{

		$logged = false;
		if($request->getSession()->get('user')){
			$logged = true;
		}

		$user = new User();
		$form = $this->createFormBuilder($user)
			->add('username')
			->add('password')
			->getForm();

		$form->handleRequest($request);



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


			if($user !== null && $user->isPasswordOk($password) && $user->getRole() !== null){
				$session = new Session();
				$session->set('user',$user->_toArrayShort());

                if($user->isModo()){
                    $session->set('modo',true);
                }

                if($user->isRedacteur()){
                    $session->set('redacteur',true);
                }

                if($user->isPartenaire()){
                    $session->set('partenaire',true);
                }
				$logged = true;
			}

		}

		return array('form' => $form->createView(),'logged' => $logged);
	}

	public function partenaireAction(){
		$session = new Session();

		if(!$session->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}


		/**
		 * @var \Acme\EsBattleBundle\Entity\User $user
		 */
		$partenaires = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Partenaire')
			->findAll();

		return $this->render('AcmeEsBattleBundle:Admin:partenaire.html.twig', array(
			'partenaires' => $partenaires
		));
	}

	public function addPartenaireAction($id, Request $request){
		$session = new Session();

		if(!$session->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}
		/**
		 * @var \Acme\EsBattleBundle\Entity\Partenaire $partenaire
		 */
		$partenaire = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Partenaire')
			->find($id);

		if($partenaire === null){
			$partenaire = new Partenaire();
		}

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findAll();


		$nom = $request->get('nom');
		$description = $request->get('description');
		$youtube = $request->get('youtube');
		$twitch = $request->get('twitch');
		$facebook = $request->get('facebook');
		$twitter = $request->get('twitter');
		$logo = $request->get('logo');
		$tuile = $request->get('tuile');
		$header = $request->get('header');
		$blocHomeLink = $request->get('blocHomeLink');
		$blocHomeImg = $request->get('blocHomeImg');

		if($nom !== null){

			$em = $this->getDoctrine()->getManager();

			$partenaire->setNom($nom);
			$partenaire->setDescription($description);
			$partenaire->setYoutube($youtube);
			$partenaire->setTwitch($twitch);
			$partenaire->setFacebook($facebook);
			$partenaire->setTwitter($twitter);
			$partenaire->setBlocHomeLink($blocHomeLink);

			$newDocumentEntity = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:Document')
				->find($logo);

			$partenaire->setLogo($newDocumentEntity);

			$newDocumentEntity = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:Document')
				->find($tuile);

			$partenaire->setTuile($newDocumentEntity);


			$newDocumentEntity = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:Document')
				->find($header);

			$partenaire->setHeader($newDocumentEntity);

			$newDocumentEntity = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:Document')
				->find($blocHomeImg);

			$partenaire->setBlocHomeImg($newDocumentEntity);

			$em->persist($partenaire);
			$em->flush();
		}

		return $this->render('AcmeEsBattleBundle:Admin:add-partenaire.html.twig', array(
			'documents' => $collectionDocument,
			'partenaire' => $partenaire
		));
	}

	public function videoAction(){
		$session = new Session();

		if(!$session->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}


		/**
		 * @var \Acme\EsBattleBundle\Entity\Video $videos
		 */
		$videos = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Video')
			->findAll();

		return $this->render('AcmeEsBattleBundle:Admin:video.html.twig', array(
			'videos' => $videos
		));
	}

	public function addVideoAction($id, Request $request){
		$session = new Session();

		if(!$session->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}
		/**
		 * @var \Acme\EsBattleBundle\Entity\Video $video
		 */
		$video = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Video')
			->find($id);

		if($video === null){
			$video = new Video();
		}

		$collectionPartenaire = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Partenaire')
			->findAll();

		$url = $request->get('url');
		$description = $request->get('description');
		$partenaire = $request->get('partenaire');

		if($url !== null){

			$em = $this->getDoctrine()->getManager();

			$video->setUrl($url);
			$video->setDescription($description);

			$newPartenaireEntity = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:Partenaire')
				->find($partenaire);

			$video->setPartenaire($newPartenaireEntity);

			$em->persist($video);
			$em->flush();
		}

		return $this->render('AcmeEsBattleBundle:Admin:add-video.html.twig', array(
			'partenaires' => $collectionPartenaire,
			'video' => $video
		));
	}

	public function addNewsAction($id, Request $request){
		$session = $request->getSession();

        if(!$session->get('modo') && !$session->get('redacteur')){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

		$user = $session->get('user');

		$user = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:User')
			->find($user['id']);

		if(!$user){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

        if($request->get('topicId')){
            $id = $request->get('topicId');
        }
		/**
		 * @var \Acme\EsBattleBundle\Entity\Topic $topic
		 */
		$topic = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Topic')
			->find($id);

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findBy(array(), array('id' => 'DESC'));

		if($topic === null){
			$topic = new Topic();
		}

		$form = $this->createFormBuilder($topic)
			->getForm();

		$form->handleRequest($request);


		if ($form->isValid()) {

			$titre = $request->get('titre');
			$vignette = $request->get('vignette');
			$document = $request->get('document');
			$texte = $request->get('message');

			$topic->setTitre($titre);
			$topic->setUser($user);
			$topic->setVisible(false);
			$topic->setStatus(Topic::STATUS_NEWS);

			$newVignetteEntity = null;
			if($vignette !== null){
				$newVignetteEntity = $this->getDoctrine()
					->getRepository('AcmeEsBattleBundle:Document')
					->find($vignette);
			}
			$topic->setVignette($newVignetteEntity);

			$newDocumentEntity = null;
			if($document !== null){
				$newDocumentEntity = $this->getDoctrine()
					->getRepository('AcmeEsBattleBundle:Document')
					->find($document);
			}
			$topic->setDocument($newDocumentEntity);

			$message = $topic->getMessages()->first();
			if($message == null){
				$message = new Message();
			}
			$message->setVisible(false);
			$message->setUser($user);
			$message->setTexte($texte);


			$em = $this->getDoctrine()->getManager();

            $topic->addMessage($message);
            $message->setTopic($topic);
            $em->persist($message);
			$em->persist($topic);
			$em->flush();

			// redirect to the show page for the just submitted item
		}

		return $this->render('AcmeEsBattleBundle:Admin:add-news.html.twig', array(
			'topic' => $topic,
			'documents' => $collectionDocument,
			'form' => $form->createView()
		));
	}

    /**
     * @Template()
     */
    public function redactionAction(){
        $collectionTopic = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Topic')
            ->findBy(array(), array('id' => 'DESC'));

        return array('topics'=>$collectionTopic);
    }
}
