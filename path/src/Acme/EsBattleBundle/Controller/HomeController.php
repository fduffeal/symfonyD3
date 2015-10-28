<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\Planification;
use Doctrine\ORM\Query\AST\Join;
use MyProject\Proxies\__CG__\stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityRepository;

use Acme\EsBattleBundle\Entity\Topic;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\True;


class HomeController extends Controller
{

    public function indexAction()
    {

        $arrayStatus = array(Topic::STATUS_NEWS);
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT topic, messages, user
            FROM AcmeEsBattleBundle:Topic topic
            JOIN topic.user user
            JOIN topic.messages messages
            WHERE topic.visible = :visible
            AND topic.status IN (:arrayStatus)
            ORDER BY topic.position ASC, topic.updated DESC'
        )->setParameter('visible', true)
            ->setParameter('arrayStatus', $arrayStatus)
            ->setMaxResults(40);

        $topicCollection = $query->getResult();

        return $this->render('AcmeEsBattleBundle:Home:index.html.twig',
            array('aTopic'=>$topicCollection)
        );
    }

    public function newsAction(){
        $response = new Response();
        return $response;
    }
}
