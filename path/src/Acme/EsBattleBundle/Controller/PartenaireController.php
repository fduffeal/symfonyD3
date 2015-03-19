<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class PartenaireController extends Controller
{

    public function indexAction(){

	    $collectionPartenaire = $this->getDoctrine()
		    ->getRepository('AcmeEsBattleBundle:Partenaire')
		    ->findAll();

        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\Partenaire $partenaire
         */
        foreach($collectionPartenaire as $partenaire){
            $aResult[] = $partenaire->_toArrayShort();
        }

	    $response = new JsonResponse();
	    $response->setPublic();
	    $response->setMaxAge(30);
	    $response->setSharedMaxAge(30);
	    $response->setData($aResult);
	    return $response;
    }

	public function getAction($id){

		/**
		 * @var \Acme\EsBattleBundle\Entity\Partenaire $partenaire
		 */
		$partenaire = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Partenaire')
			->find($id);

		$response = new JsonResponse();
		$response->setPublic();
		$response->setMaxAge(30);
		$response->setSharedMaxAge(30);
		$response->setData($partenaire->_toArray());
		return $response;
	}
}
