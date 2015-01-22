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
            $aTopic[] = $topic->_toArrayShort();
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
}
