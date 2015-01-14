<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserGame;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;
use Acme\EsBattleBundle\Entity\Annonce as Annonce;
use Acme\EsBattleBundle\Entity\Tag;

use Symfony\Component\HttpFoundation\Response;

class AnnonceController extends Controller
{
    public function createAction($tags,$description,$userGameId){
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT userGame
            FROM AcmeEsBattleBundle:UserGame userGame
            JOIN userGame.plateform plateform
            JOIN userGame.game game
            WHERE userGame.id = :userGameId'
        )->setParameter('userGameId', $userGameId);

        $result = $query->getResult();

        if(!array_key_exists(0,$result)){
            $response->setStatusCode(403);
            $content = array('msg'=> 'user game profil not found');
            $response->setContent(json_encode($content));
            return $response;
        }

        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
         */
        $userGame = $result[0];

        /**
         * @var \Acme\EsBattleBundle\Entity\Plateform $myPlateform
         */
        $myPlateform = $userGame->getPlateform();

        /**
         * @var \Acme\EsBattleBundle\Entity\Game $myGame
         */
        $myGame = $userGame->getGame();

        /**
         * @var \Acme\EsBattleBundle\Entity\Annonce $annonce
         */
        $annonce = new Annonce();
        $annonce->setDescription($description);
        $annonce->setAuthor($userGame);
        $annonce->setPlateform($myPlateform);
        $annonce->setGame($myGame);


        $aTags = preg_split("/[\s,]+/",$tags);

        $em = $this->getDoctrine()->getManager();

        foreach($aTags as $key){
            $selectedTag = $this->getDoctrine()
                ->getRepository('AcmeEsBattleBundle:Tag')
                ->findOneBy(array('nom' => $key));

            $key = trim($key);

            if($selectedTag === null && $key !== ""){
                $selectedTag = new Tag();
                $selectedTag->setNom($key);
                $selectedTag->setPoids(0);
                $em->persist($selectedTag);
            }

            if($selectedTag !== null){
                $annonce->addTag($selectedTag);
            }
        }

        $em->persist($annonce);
        $em->flush();

        $json = $annonce->_toJson();


        $response = new Response();
        $response->setContent($json);
        return $response;

    }

    public function indexAction(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT annonce
            FROM AcmeEsBattleBundle:Annonce annonce
            JOIN annonce.author author
            JOIN annonce.plateform plateform
            JOIN annonce.game game
            ORDER BY annonce.created DESC'
        )->setMaxResults(70);

        $result = $query->getResult();
        $aResult = [];
        foreach($result as $annonce){
            $aResult[] = $annonce->_toArray();
        }

        $json = json_encode($aResult);
        $response = new Response();
        $response->setContent($json);
        return $response;
    }
}
