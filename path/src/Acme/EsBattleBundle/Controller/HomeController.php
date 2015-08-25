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

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\True;


class HomeController extends Controller
{

    public function indexAction()
    {
        $response = new Response();
        return $response;
    }
}
