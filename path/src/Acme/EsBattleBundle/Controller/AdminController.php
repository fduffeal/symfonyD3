<?php

namespace Acme\EsBattleBundle\Controller;

use Acme\EsBattleBundle\Entity\UserGame;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function checkUserGameAction(){

        $response = new Response();

        $bungie = $this->get('acme_es_battle.bungie');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT user
            FROM AcmeEsBattleBundle:User user
            JOIN user.usergames usergames'
        );

        $collection = $query->getResult();

        $aUser = [];
        /**
         * @var \Acme\EsBattleBundle\Entity\User $user
         */
        foreach($collection as $user){
            $aUser[] = $user->_toArray();

            $aUserGames = $user->getUsergames();

            $aUserNames = [];
            /**
             * @var \Acme\EsBattleBundle\Entity\UserGame $usergames
             */
            foreach($aUserGames as $usergames){
                $gamerTag = $usergames->getGameUsername();
                $plaform = $usergames->getPlateform();
                $game = $usergames->getGame();
                if(in_array($gamerTag,$aUserNames)){
                    continue;
                }

                $aUserNames[] = $gamerTag;

                $characters = $bungie->getCharacters($plaform->getBungiePlateformId(),$gamerTag);
                if($characters !== null){
                    foreach($characters as $key => $character){

                        $alreadyAdded = false;
                        foreach($aUserGames as $usergames){
                            if($character['characterId'] === $usergames->getExtId()){
                                echo $character['characterId']." already added<br/>";
                                $alreadyAdded = true;
                            }
                        }

                        if($alreadyAdded === true){
                            continue;
                        }

                        $userGame = $bungie->saveGameUserInfo($character,$user,$plaform,$game);
                        echo $character['characterId']." added to".$user->getUsername()."<br/>";
                        $user->addUsergame($userGame);
                    }
                }
            }
        }


        return $response;
    }
    public function removeOldUserGameAction(){
        $response = new Response();

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT appointment
            FROM AcmeEsBattleBundle:Appointment appointment
            JOIN appointment.leader leader
            LEFT JOIN leader.usergames userGameLeader
            LEFT JOIN appointment.usersGame usersGame
            LEFT JOIN appointment.usersGameInQueue usersGameInQueue
            WHERE userGameLeader.ext_id IS NULL
            OR usersGame.ext_id IS NULL
            OR usersGameInQueue.ext_id IS NULL'
        );


        $collection = $query->getResult();

        echo sizeof($collection).'<br/>';


        $em = $this->getDoctrine()->getManager();

        /**
         * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
         * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
         */
        foreach($collection as $appointment){
            //echo $userGame->_toJson();
            $aUsersGame = $appointment->getUsersGame();

            foreach($aUsersGame as $userGameSelected){
                $userGameNew = self::changeUser($appointment,$userGameSelected);
                if($userGameNew !== false){

                    if($userGameNew !== null){
                        $appointment->addUsersGame($userGameNew);
                        echo $userGameNew->getGameProfilName().'remplace '.$userGameSelected->getGameProfilName().'<br/>';
                    }

                    $appointment->removeUsersGame($userGameSelected);
                    $em->remove($userGameSelected);
                    echo 'supprime '.$userGameSelected->getGameProfilName().'<br/>';

                }
            }

            $aUsersGame = $appointment->getUsersGameInQueue();
            foreach($aUsersGame as $userGameSelected){
                $userGameNew = self::changeUser($appointment,$userGameSelected);
                if($userGameNew !== false){

                    if($userGameNew !== null){
                        $appointment->addUsersGameInQueue($userGameNew);
                        echo $userGameNew->getGameProfilName().'remplace '.$userGameSelected->getGameProfilName().'<br/>';
                    }

                    $appointment->removeUsersGameInQueue($userGameSelected);
                    $em->remove($userGameSelected);
                    echo 'supprime '.$userGameSelected->getGameProfilName().'<br/>';
                }
            }

            if(sizeof($appointment->getUsersGameInQueue()) === 0 && sizeof($appointment->getUsersGame()) === 0 ){
                $em->remove($appointment);

                echo 'supprime rdv '.$appointment->getId().'<br/>';
            } else {
                $hasLeader = false;
                $lastUserGame = null;
                foreach($appointment->getUsersGame() as $userGame){
                    if($userGame->getUser()->getId() === $appointment->getLeader()->getId()){
                        $hasLeader = true;
                    }
                    $lastUserGame = $userGame;
                }
                if($hasLeader === false){
                    $appointment->setLeader($lastUserGame->getUser());
                    echo 'changement de leader '.$appointment->getId().'<br/>';
                }

                $em->persist($appointment);
                echo 'save rdv '.$appointment->getId().'<br/>';
            }



        }

        $em->flush();

        return $response;

    }


    public function deleteOldUserGameAction()
    {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT usergame
            FROM AcmeEsBattleBundle:UserGame usergame
            JOIN usergame.user user
            WHERE  usergame.ext_id IS NULL'
        );

        $collection = $query->getResult();

        foreach($collection as $usergame){
            $em->remove($usergame);
            echo 'remove '.$usergame->getId();
        }

        $em->flush();

        return $response;
    }

    /**
     * @var \Acme\EsBattleBundle\Entity\User $user
     * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
     *
     * @var \Acme\EsBattleBundle\Entity\Appointment $appointment
     */
    public function changeUser($appointment,$userGame){

        if($userGame->getExtId() === null){
            $user = $userGame->getUser();

            $aUserGame = $user->getUsergames();
            /**
             * @var \Acme\EsBattleBundle\Entity\UserGame $userGameSelected
             */
            foreach($aUserGame as $userGameSelected){
                if($userGameSelected->getExtId() !== null){
                    return $userGameSelected;
                }
            }

            return null;
        }

        return false;
    }
}
