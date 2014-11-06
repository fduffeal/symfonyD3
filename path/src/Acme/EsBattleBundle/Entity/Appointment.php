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
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="appointment_user",
     *      joinColumns={@ORM\JoinColumn(name="appointment_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    private $users;

	/**
	 * @ORM\ManyToMany(targetEntity="User")
	 * @ORM\JoinTable(name="appointment_user_in_queue",
	 *      joinColumns={@ORM\JoinColumn(name="appointment_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
	 *      )
	 **/
	private $usersInQueue;


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
     * Add users
     *
     * @param \Acme\EsBattleBundle\Entity\User $users
     * @return Appointment
     */
    public function addUser(\Acme\EsBattleBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Acme\EsBattleBundle\Entity\User $users
     */
    public function removeUser(\Acme\EsBattleBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
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
        return array(
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'start' => $this->getStart()->getTimestamp(),
            'duree' => $this->getDuree(),
            'nbParticipant' => $this->getNbParticipant(),
            'leader' => $this->getLeader()->_toArray(),
            'tags' => $aTags,
            'plateform' => ($plateform)?$plateform->_toArray():null,
            'game' => ($game)?$game->_toArray():null

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
}
