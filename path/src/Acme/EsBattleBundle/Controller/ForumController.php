<?php

namespace Acme\EsBattleBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Acme\EsBattleBundle\Entity\Message;
use Acme\EsBattleBundle\Entity\UserGame;
use Acme\EsBattleBundle\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use \Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;


class ForumController extends Controller
{

    public function getAllTopicAction(){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT topic
            FROM AcmeEsBattleBundle:Topic topic
            WHERE topic.visible = :visible
            ORDER BY topic.updated DESC'
        )->setParameter('visible', true)->setMaxResults(1);

        $topicCollection = $query->getResult();

        if(!$topicCollection[0]){
            return $response;
        }

        $response->setPublic();
        $response->setLastModified($topicCollection[0]->getUpdated());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }


        $query = $em->createQuery(
            'SELECT topic, messages, user
            FROM AcmeEsBattleBundle:Topic topic
            JOIN topic.user user
            JOIN topic.messages messages
            WHERE topic.visible = :visible
            ORDER BY topic.position ASC, topic.updated DESC'
        )->setParameter('visible', true);

        $topicCollection = $query->getResult();

        $aTopic = [];

        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        foreach($topicCollection as $topic){
            $aTopic[] = $topic->_toArray();
        }

        $json = json_encode($aTopic);
        $response->setContent($json);

//        throw new Exception();

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

	/**
	 * @Template()
	 */
	public function getNewsAction(){

		$format = $this->getRequest()->getRequestFormat();

		if($format === 'json') {
			$response = new JsonResponse();
		} else {
			$response = new Response();
		}

		$response->setMaxAge(300);
		$response->setSharedMaxAge(300);

		$em = $this->getDoctrine()->getManager();

		$query = $em->createQuery(
			'SELECT topic,vignette,messages
            FROM AcmeEsBattleBundle:Topic topic
            JOIN topic.messages messages
            JOIN topic.vignette vignette
            WHERE topic.visible = :visible
            AND topic.status = :newsStatus
            ORDER BY topic.created DESC'
		)->setParameter('visible', true)->setParameter('newsStatus',Topic::STATUS_NEWS);

		$topicCollection = $query->getResult();

		$response->setPublic();

		$aTopic = [];

		/**
		 * @var \Acme\EsBattleBundle\Entity\Topic $topic
		 */
		foreach($topicCollection as $topic){
			$aCurrentTopic = $topic->_toArray();
			$aCurrentTopic['message'] = $topic->getMessages()->first()->_toArrayShort();
			$aCurrentTopic['document'] = ($topic->getDocument())?$topic->getDocument()->_toArray():null;
			$aCurrentTopic['vignette'] = ($topic->getVignette())?$topic->getVignette()->_toArray():null;
			$aTopic[] = $aCurrentTopic;
		}


		if($format === 'json'){
			$response = new JsonResponse();
			$response->setData($aTopic);
			return $response;
		}

		return $aTopic;
	}

    public function createTopicAction($username,$token)
    {

        $request = $this->getRequest();

        $requestContent = json_decode($request->getContent());
        if($requestContent === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }
        $title = $requestContent->title;
        $texte = $requestContent->texte;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $token)
            );

        if($user === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        $em = $this->getDoctrine()->getManager();


        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        $topic = new Topic();
        $topic->setTitre($title);
        $topic->setUser($user);
        $topic->setPosition(1);


        /**
         * @var \Acme\EsBattleBundle\Entity\Message $message
         */
        $message = new Message();
        $message->setTexte($texte);
        $message->setUser($user);
        $message->setTopic($topic);

        $topic->addMessage($message);
        $em->persist($message);
        $em->persist($topic);

        $em->flush();

        $response->setContent($topic->_toJson());

        return $response;

    }

    /**
     * @Template()
     */
    public function getTopicAction($id,$page,$nbResult)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $nbResult = intval($nbResult);

        $start = (intval($page)-1)*$nbResult;

        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        $topic = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Topic')
            ->find($id);


//        var_dump($aMessage);
//        throw new Exception();
        $response->setPublic();
        $response->setLastModified($topic->getUpdated());

        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT topic, message,messageUser
            FROM AcmeEsBattleBundle:Message message
            JOIN message.topic topic
            JOIN message.user messageUser
            WHERE topic.visible = :visible AND topic.id = :id AND message.visible = :visible
            ORDER BY message.created ASC";

        $query = $em->createQuery($dql)
            ->setParameter('visible', true)
            ->setParameter('id', $id)
            ->setFirstResult($start)
            ->setMaxResults($nbResult);

        $messageCollection = $query->getResult();

        $aMessage = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\Message $message
         */
        foreach($messageCollection as $message){
            $aMessage[] = $message->_toArray();
        }

        $nbMessageResponse = sizeof($aMessage);

        $topic = [];
        if($nbMessageResponse !== 0){
            $topic = $messageCollection[0]->getTopic()->_toArray();
        }

        $data = array(
            'topic'   => $topic,
            'messages'  =>$aMessage);

        $format = $this->getRequest()->getRequestFormat();

        if($format === 'json'){
            $response = new JsonResponse();
            $response->setData($data);
            return $response;
        }



        return $data;

    }

    public function createMessageAction($topicId,$username,$token,$page,$nbResult)
    {
        $request = $this->getRequest();

        $requestContent = json_decode($request->getContent());
        if($requestContent === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }
        $texte = $requestContent->texte;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $token)
            );

        if($user === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        $topic = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:Topic')
            ->find($topicId);

        if($topic === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        /**
         * @var \Acme\EsBattleBundle\Entity\Message $message
         */
        $message = new Message();
        $message->setTexte($texte);
        $message->setUser($user);
        $message->setTopic($topic);

        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        $topic->setUpdatedValue();

        $em->persist($message);
        $em->flush();

        $response = $this->forward('AcmeEsBattleBundle:Forum:getTopic', array(
            'id'  => $topic->getId(),
            'page'  => $page,
            'nbResult'  => $nbResult,
	        '_format' => 'json'
        ));


        return $response;

    }

    public function updateMessageAction($messageId,$username,$token,$page,$nbResult)
    {


        $request = $this->getRequest();

        $requestContent = json_decode($request->getContent());
        if($requestContent === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }
        $texte = $requestContent->texte;

        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $token)
            );

        if($user === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        if($user->getRole() == 'modo') {
            /**
             * @var \Acme\EsBattleBundle\Entity\Message $message
             */
            $message = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:Message')
                ->find($messageId);
        } else {
            /**
             * @var \Acme\EsBattleBundle\Entity\Message $message
             */
            $message = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:Message')
                ->findOneBy(array('id'=>$messageId,'user'=>$user));
        }



        if($message === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        $message->setTexte($texte);

        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        $topic = $message->getTopic();
        $topic->setUpdatedValue();
        $em->persist($message);
        $em->persist($topic);

        $em->flush();


        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $response = $this->forward('AcmeEsBattleBundle:Forum:getTopic', array(
            'id'  => $topic->getId(),
            'page'  => $page,
            'nbResult'  => $nbResult,
	        '_format' => 'json'
        ));

        return $response;
    }

    public function deleteMessageAction($messageId,$username,$token,$page,$nbResult)
    {

        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('username' => $username,'apikey' => $token)
            );

        if($user === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        if($user->getRole() == 'modo'){
            /**
             * @var \Acme\EsBattleBundle\Entity\Message $message
             */
            $message = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:Message')
                ->find($messageId);
        } else {
            /**
             * @var \Acme\EsBattleBundle\Entity\Message $message
             */
            $message = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:Message')
                ->findOneBy(array('id'=>$messageId,'user'=>$user));
        }



        if($message === null){
            $response = new Response();
            $response->setStatusCode(401);
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        $message->setVisible(false);

        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        $topic = $message->getTopic();
        $topic->setUpdatedValue();
        $em->persist($message);
        $em->persist($topic);

        $em->flush();


        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $response = $this->forward('AcmeEsBattleBundle:Forum:getTopic', array(
            'id'  => $topic->getId(),
            'page'  => $page,
            'nbResult'  => $nbResult,
	        '_format' => 'json'
        ));

        return $response;
    }
}
