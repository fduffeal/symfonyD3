<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Acme\EsBattleBundle\Entity\Partenaire;

use Symfony\Component\HttpFoundation\JsonResponse;

class PartenaireController extends Controller
{

    public function indexAction(){

	    $em = $this->getDoctrine()->getManager();

	    $query = $em->createQuery(
		    'SELECT partenaire
            FROM AcmeEsBattleBundle:Partenaire partenaire
            JOIN partenaire.videos'
	    );

	    $collectionPartenaire = $query->getResult();

        $aResult = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\Partenaire $partenaire
         */
        foreach($collectionPartenaire as $partenaire){
            $aResult[] = $partenaire->_toArrayShort();
        }

	    $response = new JsonResponse();
	    $response->setPublic();
	    $response->setMaxAge(300);
	    $response->setSharedMaxAge(300);
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
		$response->setMaxAge(300);
		$response->setSharedMaxAge(300);
		$response->setData($partenaire->_toArray());
		return $response;
	}

	public function adminAction(Request $request){
		if(!$request->getSession()->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}
		/**
		 * @var \Doctrine\Common\Collections\ArrayCollection $partenaireCollection
		 */
		$partenaireCollection = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Partenaire')
			->findAll();

		return $this->render('AcmeEsBattleBundle:Partenaire:list.html.twig', array(
			'partenaires' => $partenaireCollection
		));
	}

	public function createAction(Request $request){

		if(!$request->getSession()->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\Partenaire $partenaire
		 */
		$partenaire = new Partenaire();

		$form = $this->createFormBuilder($partenaire)
			->add('nom')
			->add('description')
			->add('youtube')
			->add('twitch')
			->add('facebook')
			->add('twitter')
			->add('logo','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
			))
			->add('tuile','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
			))
			->add('header','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
			))
			->add('blocHomeImg','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
			))
			->add('blocHomeLink','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($partenaire);
			$em->flush();

			$response = $this->forward('AcmeEsBattleBundle:Partenaire:admin');
		} else {

			$response = $this->render('AcmeEsBattleBundle:Partenaire:form.html.twig', array(
				'form' => $form->createView()
			));
		}

		return $response;
	}

	public function updateAction($id,Request $request){

		if(!$request->getSession()->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\Partenaire $partenaire
		 */
		$partenaire = new Partenaire();

		$form = $this->createFormBuilder($partenaire)
			->add('nom')
			->add('description')
			->add('youtube')
			->add('twitch')
			->add('facebook')
			->add('twitter')
			->add('logo')
			->add('tuile')
			->add('header')
			->add('blocHomeImg')
			->add('blocHomeLink')
			->getForm();

		if($partenaire !== null){
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->flush();
			}
		}

		$response = $this->render('AcmeEsBattleBundle:Partenaire:form.html.twig', array(
			'partenaire'  => $partenaire,
			'form' => $form->createView()
		));

		return $response;
	}

	public function deleteAction($id){
		$session = new Session();

		if(!$session->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		$planification = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Planification')
			->find($id);

		if($planification !== null){
			$em = $this->getDoctrine()->getManager();
			$em->remove($planification);

			$em->flush();
		}

		$response = $this->forward('AcmeEsBattleBundle:Planification:list');

		return $response;
	}
}
