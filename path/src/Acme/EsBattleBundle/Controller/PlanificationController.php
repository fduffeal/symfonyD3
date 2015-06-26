<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\Planification;
use Doctrine\ORM\Query\AST\Join;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\Tools\Pagination\Paginator;

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
//		$response->setMaxAge(300);
//		$response->setSharedMaxAge(300);

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

	public function listAction(Request $request,$offset,$limit){

		$nbContentByPage = $limit;
		$em = $this->getDoctrine()->getManager();
		$response = $this->forward('AcmeEsBattleBundle:Planification:index');

		$content = $response->getContent();
		$displayed = json_decode($content);

		if($displayed && !property_exists($displayed,'id')){
			$displayed = null;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\User $user
		 */
		$user = unserialize($request->getSession()->get('user'));

		$query = $em->createQuery(
			'SELECT userPartenaire.id
            FROM AcmeEsBattleBundle:UserPartenaire userPartenaire
            INNER JOIN  AcmeEsBattleBundle:User user
            WHERE user.id = :id'
		)->setParameter('id',$user->getId());

		$userPartenaires = $query->getResult();

		/**
		 * @var \Acme\EsBattleBundle\Entity\UserPartenaire $userPartenaire
		 */
		foreach($userPartenaires as $userPartenaire){
			$arrayIdCurrentPartenaire[] = $userPartenaire['id'];
		}

		$query = $em->createQuery(
			'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            ORDER BY planification.start DESC'
		)->setFirstResult($offset)
		->setMaxResults($nbContentByPage);

		$paginator = new Paginator($query, false);

		$c = count($paginator);

		return $this->render('AcmeEsBattleBundle:Planification:list.html.twig', array(
			'nbContentByPage'=> $nbContentByPage,
			'offset'=> $offset,
			'current' => $displayed,
			'planifications' => $paginator,
			'nbPage' => ceil($c/$nbContentByPage),
			'arrayIdCurrentPartenaire' => $arrayIdCurrentPartenaire
		));
	}

	/**
	 * @var \Acme\EsBattleBundle\Entity\Planification $planification
	 */
	private function _getPanifInSameTime($planification){
		$em = $this->getDoctrine()->getManager();

		if($planification->getId() !== null){
			$query = $em->createQuery(
				'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            where ((planification.start >=  :start and planification.start <= :end)
            OR (planification.end <=  :end and planification.end >= :start)
            OR (planification.start <=  :start and planification.end >= :end))
            and planification.id != :currentId
            ORDER BY planification.start'
			)->setParameter('start', $planification->getStart())
				->setParameter('end', $planification->getEnd())
				->setParameter('currentId', $planification->getId());
		} else {
			$query = $em->createQuery(
				'SELECT planification
            FROM AcmeEsBattleBundle:Planification planification
            where ((planification.start >=  :start and planification.start <= :end)
            OR (planification.end <=  :end and planification.end >= :start)
            OR (planification.start <=  :start and planification.end >= :end))
			ORDER BY planification.start'
			)->setParameter('start', $planification->getStart())
				->setParameter('end', $planification->getEnd());
		}


		$collection = $query->getResult();

		return $collection;
	}

	public function createAction(Request $request){

		if(!$request->getSession()->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}
		/**
		 * @var \Acme\EsBattleBundle\Entity\User $user
		 */
		$user = unserialize($request->getSession()->get('user'));

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		$planification = new Planification();

		$form = self::getForm($planification,$user);

		$form->handleRequest($request);
		$otherPlanification = null;

		if ($form->isValid()) {

			$otherPlanification = $this->_getPanifInSameTime($planification);
			if(empty ($otherPlanification)){
				$em = $this->getDoctrine()->getManager();
				$em->persist($planification);
				$em->flush();
				return $this->redirect($this->generateUrl('acme_es_battle_planification_admin'), 301);
			}
		}

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findAll();


		$response = $this->render('AcmeEsBattleBundle:Planification:form.html.twig', array(
			'planification'  => $planification,
			'form' => $form->createView(),
			'documents' => $collectionDocument,
			'otherPlanifications'=>$otherPlanification
		));

		return $response;
	}

	/**
	 * @var \Acme\EsBattleBundle\Entity\Planification $planification
	 * @var \Acme\EsBattleBundle\Entity\User $user
	 */
	public function getForm($planification,$user){

		$queryVideos =  $this->getDoctrine()->getRepository('AcmeEsBattleBundle:Video')->createQueryBuilder('v')
			->innerJoin('v.partenaire', 'p', Join::JOIN_TYPE_INNER)
			->innerJoin('p.userpartenaires', 'up', Join::JOIN_TYPE_INNER)
			->innerJoin('up.user', 'u', Join::JOIN_TYPE_INNER)
			->where('u.id = :currentUserId')
			->setParameter('currentUserId',$user->getId())
			->orderBy('v.description', 'ASC');

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
				'query_builder' => $queryVideos,
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
		 * @var \Acme\EsBattleBundle\Entity\User $user
		 */
		$user = unserialize($request->getSession()->get('user'));

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		$planification = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Planification')->createQueryBuilder('planif')
			->innerJoin('planif.video', 'v', Join::JOIN_TYPE_INNER)
			->innerJoin('v.partenaire','part', Join::JOIN_TYPE_INNER)
			->innerJoin('part.userpartenaires','up', Join::JOIN_TYPE_INNER)
			->innerJoin('up.user','u', Join::JOIN_TYPE_INNER)
			->where('u.id = :currentUserId')
			->andWhere('planif.id = :id')
			->setParameter('currentUserId',$user->getId())
			->setParameter('id',$id)->getQuery()->getResult();

		if(empty($planification)) {
			return $this->redirect($this->generateUrl('acme_es_battle_planification_admin'), 301);
		}

		$planification = $planification[0];

		$form = self::getForm($planification,$user);

		$otherPlanification = null;

		$form->handleRequest($request);

		if ($form->isValid()) {
			$otherPlanification = $this->_getPanifInSameTime($planification);
			if(empty($otherPlanification)){
				$em = $this->getDoctrine()->getManager();
				$em->flush();

				return $this->redirect($this->generateUrl('acme_es_battle_planification_admin'), 301);
			}
		}

		$collectionDocument = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Document')
			->findAll();

		$response = $this->render('AcmeEsBattleBundle:Planification:form.html.twig', array(
			'planification'  => $planification,
			'form' => $form->createView(),
			'documents' => $collectionDocument,
			'otherPlanifications'=>$otherPlanification
		));

		return $response;
	}

	public function deleteAction($id,Request $request){
		$session = $request->getSession();

		if(!$session->get('modo')){
			$response = new Response();
			$response->setStatusCode(401);
			return $response;
		}

		/**
		 * @var \Acme\EsBattleBundle\Entity\User $user
		 */
		$user = unserialize($request->getSession()->get('user'));

		/**
		 * @var \Acme\EsBattleBundle\Entity\Planification $planification
		 */
		$planification = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:Planification')->createQueryBuilder('planif')
			->innerJoin('planif.video', 'v', Join::JOIN_TYPE_INNER)
			->innerJoin('v.partenaire','part', Join::JOIN_TYPE_INNER)
			->innerJoin('part.userpartenaires','up', Join::JOIN_TYPE_INNER)
			->innerJoin('up.user','u', Join::JOIN_TYPE_INNER)
			->where('u.id = :currentUserId')
			->andWhere('planif.id = :id')
			->setParameter('currentUserId',$user->getId())
			->setParameter('id',$id)->getQuery()->getResult();

		if(empty($planification)) {
			return $this->redirect($this->generateUrl('acme_es_battle_planification_admin'), 301);
		}

		$planification = $planification[0];

		if($planification !== null){
			$em = $this->getDoctrine()->getManager();
			$em->remove($planification);

			$em->flush();
		}

		return $this->redirect($this->generateUrl('acme_es_battle_planification_admin'), 301);
	}
}
