<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\Planification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class PlanificationController extends Controller
{

	public function indexAction(){
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            WHERE planification.start < CURRENT_TIMESTAMP()
            AND planification.end > CURRENT_TIMESTAMP()
            ORDER BY planification.start DESC'
		)->setMaxResults(1);

		$collection = $query->getResult();

		$response = new JsonResponse();

		if(!$collection){
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $collection[0]
		 */
		$response->setData($collection[0]->_toArray());

		return $response;
	}

	public function listAction(){
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            WHERE planification.start < CURRENT_TIMESTAMP()
            AND planification.end > CURRENT_TIMESTAMP()
            ORDER BY planification.start DESC'
		)->setMaxResults(1);

		$displayed = $query->getResult();

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

	public function retrieveAction($id){
		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		$planification = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Planification')
			->find($id);
		$response = new JsonResponse();

		if(!$planification){
			return $response;
		}

		return $this->render('AcmeEsBattleBundle:Planification:retrieve.html.twig', array(
			'planification' => $planification
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

		$form = $this->createFormBuilder($planification)
			->add('titre')
			->add('description')
			->add('start')
			->add('end')
			->add('video', 'entity', array(
				'empty_value' => 'Choisissez une vidéo',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Video',
				'property' => 'description',
			))
			->add('image', 'entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'path',
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($planification);
			$em->flush();

			$response = $this->forward('AcmeEsBattleBundle:Planification:list');
		} else {

			$response = $this->render('AcmeEsBattleBundle:Planification:retrieve.html.twig', array(
				'planification'  => $planification,
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
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		$planification = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Planification')
			->find($id);

		$form = $this->createFormBuilder($planification)
			->add('titre')
			->add('description')
			->add('start')
			->add('end')
			->add('video', 'entity', array(
				'empty_value' => 'Choisissez une vidéo',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Video',
				'property' => 'description',
			))
			->add('image', 'entity', array(
				'empty_value' => 'Choisissez une image',
				'required' => false,
				'class' => 'AcmeEsBattleBundle:Document',
				'property' => 'path',
			))
			->getForm();

		if($planification !== null){
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->flush();
			}
		}

		$response = $this->render('AcmeEsBattleBundle:Planification:retrieve.html.twig', array(
			'planification'  => $planification,
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
