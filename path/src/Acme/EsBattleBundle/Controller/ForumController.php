<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\Message;
use Acme\EsBattleBundle\Entity\UserGame;
use Acme\EsBattleBundle\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use \Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Config\Definition\Exception\Exception;


class ForumController extends Controller
{

    public function getAllTopicAction(){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT topic, messages, user
            FROM AcmeEsBattleBundle:Topic topic
            JOIN topic.user user
            JOIN topic.messages messages
            WHERE topic.visible = :visible
            ORDER BY topic.position ASC'
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
        $response->setPublic();
        // définit l'âge max des caches privés ou des caches partagés
        $response->setMaxAge(60);
        $response->setSharedMaxAge(60);

//        throw new Exception();

        return $response;
    }

    public function createTopicAction($title,$texte,$username,$token)
    {
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
         * @var \Acme\EsBattleBundle\Entity\Message $message
         */
        $message = new Message();
        $message->setTexte($texte);

        /**
         * @var \Acme\EsBattleBundle\Entity\Topic $topic
         */
        $topic = new Topic();
        $topic->setTitre($title);
        $topic->addMessage($message);

        $em->persist($topic);

        $response->setContent($topic->_toJson());

        return $response;

    }

    public function getTopicAction($id,$page,$nbResult)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $nbResult = intval($nbResult);

        $start = (intval($page)-1)*$nbResult;


        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT message
            FROM AcmeEsBattleBundle:Message message
            JOIN message.topic topic
            WHERE topic.id = :id AND message.visible = :visible
            ORDER BY message.updated DESC, message.created DESC";


        $query = $em->createQuery($dql)
            ->setParameter('visible', true)
            ->setParameter('id', $id)
            ->setFirstResult($start)
            ->setMaxResults($nbResult);

        $messageCollection = $query->getResult();

        $aIdList = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\Message $message
         */
        foreach($messageCollection as $message){
            $aIdList[] = $message->getId();
        }

        $nbMessageResponse = sizeof($aIdList);
        /**
         * @var \Acme\EsBattleBundle\Entity\Message $lastMessage
         */
        $lastMessage = null;
        $lastModifiedDate = null;
        if($nbMessageResponse !== 0){
            $lastMessage = $messageCollection[0];

            $lastCreated = $lastMessage->getCreated();
            $lastUpdated = $lastMessage->getUpdated();

            $lastModifiedDate = $lastCreated;
            if($lastUpdated > $lastCreated){
                $lastModifiedDate = $lastUpdated;
            }
        }

//        var_dump($aMessage);
//        throw new Exception();
        $response->setPublic();
        $response->setETag('TOPIC_'.$id.'_'.$page.'_'.$nbResult.'_'.$nbMessageResponse.'_'.implode('_',$aIdList));
        $response->setLastModified($lastModifiedDate);


        // Vérifie que l'objet Response n'est pas modifié
        // pour un objet Request donné
        if ($response->isNotModified($this->getRequest())) {
            // Retourne immédiatement un objet 304 Response
            return $response;
        }

        $dql = "SELECT topic, message,messageUser
            FROM AcmeEsBattleBundle:Message message
            JOIN message.topic topic
            JOIN message.user messageUser
            WHERE topic.visible = :visible AND topic.id = :id AND message.visible = :visible
            ORDER BY message.updated DESC, message.created DESC";

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

        $topic = [];
        if($nbMessageResponse !== 0){
            $topic = $messageCollection[0]->getTopic()->_toArray();
        }

        $data = array(
            'topic'   => $topic,
            'messages'  =>$aMessage);

        $response->setContent(json_encode($data));
        return $response;

    }
}
