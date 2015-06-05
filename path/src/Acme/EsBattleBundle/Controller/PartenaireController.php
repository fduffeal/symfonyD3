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
			->add('nom','text',array(
				'required' => true,
				'attr' => array('class'=>'form-control')
			))
			->add('description','textarea',array(
				'required' => true,
				'attr' => array('class'=>'form-control')
			))
			->add('youtube','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('twitch','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('facebook','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('twitter','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('logo','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('tuile','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('header','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('blocHomeImg','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('blocHomeLink','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($partenaire);
			$em->flush();

			$response = $this->forward('AcmeEsBattleBundle:Partenaire:admin');
		} else {

			$collectionDocument = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:Document')
				->findAll();

			$response = $this->render('AcmeEsBattleBundle:Partenaire:form.html.twig', array(
				'partenaire'  => $partenaire,
				'form' => $form->createView(),
				'documents' => $collectionDocument
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
		$partenaire = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Partenaire')
			->find($id);

		$form = $this->createFormBuilder($partenaire)
			->add('nom','text',array(
				'required' => true,
				'attr' => array('class'=>'form-control')
			))
			->add('description','textarea',array(
				'required' => true,
				'attr' => array('class'=>'form-control')
			))
			->add('youtube','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('twitch','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('facebook','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('twitter','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->add('logo','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('tuile','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('header','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('blocHomeImg','entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->add('blocHomeLink','text',array(
				'required' => false,
				'attr' => array('class'=>'form-control')
			))
			->getForm();

		if($partenaire !== null){
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->flush();
			}
		}

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findAll();

		$response = $this->render('AcmeEsBattleBundle:Partenaire:form.html.twig', array(
			'partenaire'  => $partenaire,
			'form' => $form->createView(),
			'documents' => $collectionDocument
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
		 * @var \Acme\EsBattleBundle\Entity\Partenaire $partenaire
		 */
		$partenaire = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Partenaire')
			->find($id);

		if($partenaire !== null){
			$em = $this->getDoctrine()->getManager();
			$em->remove($partenaire);

			$em->flush();
		}

		$response = $this->forward('AcmeEsBattleBundle:Planification:list');

		return $response;
	}
}
