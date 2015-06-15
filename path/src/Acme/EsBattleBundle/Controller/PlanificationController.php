<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\Planification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;

use Symfony\Component\HttpFoundation\Response;


class PlanificationController extends Controller
{

	public function indexAction(){
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            ORDER BY planification.updated DESC'
		)->setMaxResults(1);

		$collection = $query->getResult();

		$response = new JsonResponse();

		$response->setPublic();
		$response->setMaxAge(300);
		$response->setSharedMaxAge(300);

		if(!$collection){
			return $response;
		}

		$response->setLastModified($collection[0]->getUpdated());

		// Vérifie que l'objet Response n'est pas modifié
		// pour un objet Request donné
		if ($response->isNotModified($this->getRequest())) {
			// Retourne immédiatement un objet 304 Response
			return $response;
		}

		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            WHERE planification.start < CURRENT_TIMESTAMP()
            AND planification.end > CURRENT_TIMESTAMP()
            ORDER BY planification.start DESC'
		)->setMaxResults(1);

		$collection = $query->getResult();

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $collection[0]
		 */
		if($collection){
			$response->setData($collection[0]->_toArray());
		}

		return $response;
	}

	public function nextAction(){
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            ORDER BY planification.updated DESC'
		)->setMaxResults(1);

		$collection = $query->getResult();

		$response = new JsonResponse();

		$response->setPublic();
		$response->setMaxAge(300);
		$response->setSharedMaxAge(300);

		if(!$collection){
			return $response;
		}

		$response->setLastModified($collection[0]->getUpdated());

		// Vérifie que l'objet Response n'est pas modifié
		// pour un objet Request donné
		if ($response->isNotModified($this->getRequest())) {
			// Retourne immédiatement un objet 304 Response
			return $response;
		}

		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            WHERE planification.end > CURRENT_TIMESTAMP()
            AND planification.start > CURRENT_TIMESTAMP()
            ORDER BY planification.start ASC'
		)->setMaxResults(5);

		$collection = $query->getResult();

		$aResult = [];
		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		foreach($collection as $planification){
			$aResult[] = $planification->_toArray();
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $collection[0]
		 */
		$response->setData($aResult);

		return $response;
	}

	public function listAction(){
		$em = $this->getDoctrine()->getManager();
		$response = $this->forward('AcmeEsBattleBundle:Planification:index');

		$content = $response->getContent();
		$displayed = json_decode($content);

		if($displayed && !property_exists($displayed,'id')){
			$displayed = null;
		}

		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            ORDER BY planification.start DESC'
		);

		$all = $query->getResult();

		return $this->render('AcmeEsBattleBundle:Planification:list.html.twig', array(
			'current' => $displayed,
			'planifications' => $all
		));
	}

	public function createAction(Request $request){

		if(!$request->getSession()->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		$planification = new Planification();

		$form = self::getForm($planification);

		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($planification);
			$em->flush();

			$response = $this->forward('AcmeEsBattleBundle:Planification:list');
		} else {

			$collectionDocument = $this->getDoctrine()
				->getRepository('AcmeEsBattleBundle:Document')
				->findAll();

			$response = $this->render('AcmeEsBattleBundle:Planification:form.html.twig', array(
				'planification'  => $planification,
				'form' => $form->createView(),
				'documents' => $collectionDocument
			));
		}

		return $response;
	}

	public function getForm($planification){
		return $this->createFormBuilder($planification)
			->add('titre','text',array('attr' => array('class'=>'form-control')))
			->add('description','textarea',array('attr' => array('class'=>'form-control')))
			->add('start','datetime', array(
				'required' => true,
				'date_widget' => 'single_text',
				'time_widget' => 'single_text',
				'attr' => array('class'=>'datepicker')
			))
			->add('end','datetime', array(
				'required' => true,
				'date_widget' => 'single_text',
				'time_widget' => 'single_text',
				'attr' => array('class'=>'datepicker')
			))
			->add('video', 'entity', array(
				'empty_value' => 'Choisissez une vidéo',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Video',
				'property' => 'description',
				'attr' => array('class'=>'form-control')
			))
			->add('image', 'entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'name',
				'attr' => array('class'=>'form-control')
			))
			->getForm();
	}

	public function updateAction($id,Request $request){

		if(!$request->getSession()->get('modo')){
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

		$form = self::getForm($planification);

		if($planification !== null){
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->flush();
			}
		}

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findAll();

		$response = $this->render('AcmeEsBattleBundle:Planification:form.html.twig', array(
			'planification'  => $planification,
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
