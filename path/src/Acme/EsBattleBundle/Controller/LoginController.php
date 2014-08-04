<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User;

class LoginController extends Controller
{
    public function indexAction($login,$password)
    {


        /*$user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findByLogin($login);*/

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('login' => $login,'password' => $password)
            );


        return $this->render('AcmeEsBattleBundle:Default:index.html.twig', array('user' => $user));
    }
}
