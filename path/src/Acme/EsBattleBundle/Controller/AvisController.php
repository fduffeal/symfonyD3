<?php

namespace Acme\EsBattleBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;


class AvisController extends Controller
{

	/**
	 * @Template()
	 * @return array|JsonResponse|Response
	 */
    public function getAction($id){
	    $format = $this->getRequest()->getRequestFormat();

	    if($format === 'json') {
		    $response = new JsonResponse();
	    } else {
		    $response = new Response();
	    }

	    $response->setPublic();
	    $response->setSharedMaxAge(600);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT avis
            FROM AcmeEsBattleBundle:UserAvis avis
            JOIN avis.user user
            WHERE user.id = :id'
        )->setParameter('id', $id);

        $avisCollection = $query->getResult();

        $aAvis = [];

        /**
         * @var \Acme\EsBattleBundle\Entity\UserAvis $avis
         */
        foreach($avisCollection as $avis){
	        $aAvis[] = $avis->_toArray();
        }

	    if($format === 'json'){
		    $response->setData($aAvis);
		    return $response;
	    }

	    return array('aAvis' => $aAvis);
    }

    public function postAction($id)
    {
	    $request = $this->getRequest();

	    $format = $request->getRequestFormat();

	    if($format === 'json') {
		    $response = new JsonResponse();
	    } else {
		    $response = new Response();
	    }

	    $requestContent = json_decode($request->getContent());
	    if($requestContent === null){
		    $response = new Response();
		    $response->setStatusCode(401);
		    return $response;
	    }
	    $texte = $requestContent->texte;
	    $username = $requestContent->username;
	    $token = $requestContent->token;


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

    }

    public function deleteAction($id)
    {

    }

    public function updateAction($id)
    {

    }
}
