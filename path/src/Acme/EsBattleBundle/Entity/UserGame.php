<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Class UserGame
 * @package Acme\EsBattleBundle\Entity
 * @ORM\Table(name="user_game")
 * @ORM\Entity
 */
class UserGame {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="users")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="games")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    protected $game;


    /**
     * @ORM\ManyToOne(targetEntity="Plateform", inversedBy="plateforms")
     * @ORM\JoinColumn(name="plateform_id", referencedColumnName="id")
     */
    protected $plateform;


    /**
     * @var string
     *
     * @ORM\Column(name="game_profil_name", type="string", length=255)
     */
    private $game_profil_name;

    /**
     * @var string
     *
     * @ORM\Column(name="game_username", type="string", length=255)
     */
    private $game_username;

    /**
     * @var string
     *
     * @ORM\Column(name="data_1", type="string", length=255)
     */
    private  $data_1;


    /**
     * @var string
     *
     * @ORM\Column(name="data_2", type="string", length=255)
     */
    private  $data_2;

    /**
     * @var string
     *
     * @ORM\Column(name="data_3", type="string", length=255)
     */
    private  $data_3;

    /**
     * @var string
     *
     * @ORM\Column(name="data_4", type="string", length=255)
     */
    private  $data_4;




    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set game_profil_name
     *
     * @param string $gameProfilName
     * @return UserGame
     */
    public function setGameProfilName($gameProfilName)
    {
        $this->game_profil_name = $gameProfilName;

        return $this;
    }

    /**
     * Get game_profil_name
     *
     * @return string 
     */
    public function getGameProfilName()
    {
        return $this->game_profil_name;
    }

    /**
     * Set data_1
     *
     * @param string $data1
     * @return UserGame
     */
    public function setData1($data1)
    {
        $this->data_1 = $data1;

        return $this;
    }

    /**
     * Get data_1
     *
     * @return string 
     */
    public function getData1()
    {
        return $this->data_1;
    }

    /**
     * Set data_2
     *
     * @param string $data2
     * @return UserGame
     */
    public function setData2($data2)
    {
        $this->data_2 = $data2;

        return $this;
    }

    /**
     * Get data_2
     *
     * @return string 
     */
    public function getData2()
    {
        return $this->data_2;
    }

    /**
     * Set data_3
     *
     * @param string $data3
     * @return UserGame
     */
    public function setData3($data3)
    {
        $this->data_3 = $data3;

        return $this;
    }

    /**
     * Get data_3
     *
     * @return string 
     */
    public function getData3()
    {
        return $this->data_3;
    }

    /**
     * Set data_4
     *
     * @param string $data4
     * @return UserGame
     */
    public function setData4($data4)
    {
        $this->data_4 = $data4;

        return $this;
    }

    /**
     * Get data_4
     *
     * @return string 
     */
    public function getData4()
    {
        return $this->data_4;
    }

    /**
     * Set user
     *
     * @param \Acme\EsBattleBundle\Entity\User $user
     * @return UserGame
     */
    public function setUser(\Acme\EsBattleBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Acme\EsBattleBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set game
     *
     * @param \Acme\EsBattleBundle\Entity\Game $game
     * @return UserGame
     */
    public function setGame(\Acme\EsBattleBundle\Entity\Game $game = null)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * Get game
     *
     * @return \Acme\EsBattleBundle\Entity\Game 
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Set plateform
     *
     * @param \Acme\EsBattleBundle\Entity\Plateform $plateform
     * @return UserGame
     */
    public function setPlateform(\Acme\EsBattleBundle\Entity\Plateform $plateform = null)
    {
        $this->plateform = $plateform;

        return $this;
    }

    /**
     * Get plateform
     *
     * @return \Acme\EsBattleBundle\Entity\Plateform 
     */
    public function getPlateform()
    {
        return $this->plateform;
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function _toArray()
    {
        return array(
            'id' => $this->getId(),
            'gameProfilName' => $this->getGameProfilName(),
            'gameUsername' => $this->getGameUsername(),
            'plateform' => $this->getPlateform()->_toArray(),
            'game' => $this->getGame()->_toArray(),
            'data1' => $this->getData1(),
            'data2' => $this->getData2(),
            'data3' => $this->getData3(),
            'data4' => $this->getData4(),
        );
    }

    public function _toJson(){
        $aUserGame = $this->_toArray();

        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($aUserGame, 'json');
    }

    /**
     * Set game_username
     *
     * @param string $gameUsername
     * @return UserGame
     */
    public function setGameUsername($gameUsername)
    {
        $this->game_username = $gameUsername;

        return $this;
    }

    /**
     * Get game_username
     *
     * @return string 
     */
    public function getGameUsername()
    {
        return $this->game_username;
    }
}
