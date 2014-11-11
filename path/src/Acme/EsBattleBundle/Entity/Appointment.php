<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Appointment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Appointment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var integer
     *
     * @ORM\Column(name="duree", type="integer")
     */
    private $duree;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime")
     */
    private $end;

	/**
	 *
	 * @ORM\Column(name="nbParticipant", type="integer")
	 */
	private $nbParticipant;


    /**
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="appointment_tag",
     *      joinColumns={@ORM\JoinColumn(name="appointment_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     **/
    private $tags;

    /**
     * @ORM\ManyToMany(targetEntity="UserGame")
     * @ORM\JoinTable(name="appointment_user_game",
     *      joinColumns={@ORM\JoinColumn(name="appointment_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_game_id", referencedColumnName="id")}
     *      )
     **/
    private $usersGame;

	/**
	 * @ORM\ManyToMany(targetEntity="UserGame")
	 * @ORM\JoinTable(name="appointment_user_game_in_queue",
	 *      joinColumns={@ORM\JoinColumn(name="appointment_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="user_game_id", referencedColumnName="id")}
	 *      )
	 **/
	private $usersGameInQueue;


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="users")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $leader;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="games")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    protected $game;

    /**
     * @ORM\ManyToOne(targetEntity="Plateform", inversedBy="plateform")
     * @ORM\JoinColumn(name="plateform_id", referencedColumnName="id")
     */
    protected $plateform;

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
     * Set description
     *
     * @param string $description
     * @return Evenement
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return Evenement
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set nbParticipant
     *
     * @param integer $nbParticipant
     * @return Appointment
     */
    public function setNbParticipant($nbParticipant)
    {
        $this->nbParticipant = $nbParticipant;

        return $this;
    }

    /**
     * Get nbParticipant
     *
     * @return integer 
     */
    public function getNbParticipant()
    {
        return $this->nbParticipant;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tags
     *
     * @param \Acme\EsBattleBundle\Entity\Tag $tags
     * @return Appointment
     */
    public function addTag(\Acme\EsBattleBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Acme\EsBattleBundle\Entity\Tag $tags
     */
    public function removeTag(\Acme\EsBattleBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set leader
     *
     * @param \Acme\EsBattleBundle\Entity\User $leader
     * @return Appointment
     */
    public function setLeader(\Acme\EsBattleBundle\Entity\User $leader = null)
    {
        $this->leader = $leader;

        return $this;
    }

    /**
     * Get leader
     *
     * @return \Acme\EsBattleBundle\Entity\User 
     */
    public function getLeader()
    {
        return $this->leader;
    }

    /**
     * Set game
     *
     * @param \Acme\EsBattleBundle\Entity\Game $game
     * @return Appointment
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


    /*
    * Serializes appointment.
    *
    * The serialized data have to contain the fields used by the equals method and the username.
    *
    * @return string
    */
    public function _toArray()
    {

        $tags = $this->getTags();
        $aTags = array();
        foreach($tags as $tag){
            $aTags[] = $tag->_toArray();
        }

        $plateform = $this->getPlateform();
        $game = $this->getGame();

        $users = $this->getUsersGame();
        $aUsers = array();
	    if($users !== null){
		    foreach($users as $user){
                $userAccount = $user->getUser();
                $user = $user->_toArray();
                $user['user'] = $userAccount->_toArray();
			    $aUsers[] = $user;
		    }
	    }

        $usersInQueue = $this->getUsersGameInQueue();
        $aUsersInQueue = array();

	    if($usersInQueue !== null){
		    foreach($usersInQueue as $userInQueue){
                $userAccount = $userInQueue->getUser();

                $userInQueue = $userInQueue->_toArray();
                $userInQueue['user'] = $userAccount->_toArray();

			    $aUsersInQueue[] = $userInQueue;
		    }
	    }

        return array(
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'start' => $this->getStart()->getTimestamp(),
            'end' => $this->getEnd()->getTimestamp(),
            'duree' => $this->getDuree(),
            'nbParticipant' => $this->getNbParticipant(),
            'leader' => $this->getLeader()->_toArray(),
            'tags' => $aTags,
            'plateform' => ($plateform)?$plateform->_toArray():null,
            'game' => ($game)?$game->_toArray():null,
            'users' => $aUsers,
            'usersInQueue' => $aUsersInQueue

        );
    }

    public function _toJson(){
        $aAppointment = $this->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($aAppointment, 'json');
    }

    /**
     * Set duree
     *
     * @param integer $duree
     * @return Appointment
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return integer 
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set plateform
     *
     * @param \Acme\EsBattleBundle\Entity\Plateform $plateform
     * @return Appointment
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
     * Add usersInQueue
     *
     * @param \Acme\EsBattleBundle\Entity\User $usersInQueue
     * @return Appointment
     */
    public function addUsersInQueue(\Acme\EsBattleBundle\Entity\User $usersInQueue)
    {
        $this->usersInQueue[] = $usersInQueue;

        return $this;
    }

    /**
     * Remove usersInQueue
     *
     * @param \Acme\EsBattleBundle\Entity\User $usersInQueue
     */
    public function removeUsersInQueue(\Acme\EsBattleBundle\Entity\User $usersInQueue)
    {
        $this->usersInQueue->removeElement($usersInQueue);
    }

    /**
     * Get usersInQueue
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersInQueue()
    {
        return $this->usersInQueue;
    }

    /**
     * Add usersGame
     *
     * @param \Acme\EsBattleBundle\Entity\UserGame $usersGame
     * @return Appointment
     */
    public function addUsersGame(\Acme\EsBattleBundle\Entity\UserGame $usersGame)
    {
        $this->usersGame[] = $usersGame;

        return $this;
    }

    /**
     * Remove usersGame
     *
     * @param \Acme\EsBattleBundle\Entity\UserGame $usersGame
     */
    public function removeUsersGame(\Acme\EsBattleBundle\Entity\UserGame $usersGame)
    {
        $this->usersGame->removeElement($usersGame);
    }

    /**
     * Get usersGame
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersGame()
    {
        return $this->usersGame;
    }

    /**
     * Add usersGameInQueue
     *
     * @param \Acme\EsBattleBundle\Entity\UserGame $usersGameInQueue
     * @return Appointment
     */
    public function addUsersGameInQueue(\Acme\EsBattleBundle\Entity\UserGame $usersGameInQueue)
    {
        $this->usersGameInQueue[] = $usersGameInQueue;

        return $this;
    }

    /**
     * Remove usersGameInQueue
     *
     * @param \Acme\EsBattleBundle\Entity\UserGame $usersGameInQueue
     */
    public function removeUsersGameInQueue(\Acme\EsBattleBundle\Entity\UserGame $usersGameInQueue)
    {
        $this->usersGameInQueue->removeElement($usersGameInQueue);
    }

    /**
     * Get usersGameInQueue
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersGameInQueue()
    {
        return $this->usersGameInQueue;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Appointment
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }
}
