<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserAvis;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;


class AvisController extends Controller
{

	public function optionsAction(){
		$format = $this->getRequest()->getRequestFormat();

		if($format === 'json') {
			$response = new JsonResponse();
		} else {
			$response = new Response();
		}
		return $response;
	}
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
	    $response->setSharedMaxAge(10);

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

	/**
	 * @param $id
	 * @return JsonResponse
	 */
    public function postAction($id)
    {

	    $request = $this->getRequest();
	    $response = new JsonResponse();
	    $requestContent = json_decode($request->getContent());

	    if($requestContent === null){
		    $response->setStatusCode(401);
		    return $response;
	    }
	    $token = $request->headers->get('Token');
	    $userId = $request->headers->get('User');

	    $avis = $requestContent->avis;

	    /**
	     * @var \Acme\EsBattleBundle\Entity\User $user
	     */
	    $user = $this->getDoctrine()
		    ->getRepository('AcmeEsBattleBundle:User')
		    ->find($id);

	    if($user === null){
		    $response->setStatusCode(401);
		    return $response;
	    }

	    /**
	     * @var \Acme\EsBattleBundle\Entity\User $auteur
	     */
	    $auteur = $this->getDoctrine()
		    ->getRepository('AcmeEsBattleBundle:User')
		    ->find($userId);

	    if($auteur === null || $auteur->getApikey() !== $token){
		    $response->setStatusCode(401);
		    return $response;
	    }

	    /**
	     * @var \Acme\EsBattleBundle\Entity\UserAvis $userAvis
	     */
	    $userAvis = new UserAvis();
	    $userAvis->setAuteur($auteur);
	    $userAvis->setAvis($avis);
	    $userAvis->setUser($user);

	    $em = $this->getDoctrine()->getManager();
	    $em->persist($userAvis);
	    $em->flush();

	    $response = $this->forward('AcmeEsBattleBundle:Avis:get', array(
		    'id'  => $id,
		    '_format' => 'json'
	    ));

	    return $response;

    }

    public function deleteAction($id)
    {

    }

    public function updateAction($id)
    {

    }
}
