<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Notification
 *
 * @ORM\Table(name="Notification",indexes={@ORM\Index(name="created_idx", columns={"created"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Notification
{
	const NEW_PLAYER_JOIN = "new_player_join";
	const YOU_HAVE_BEEN_ACCEPTED = "you_have_been_accepted";
	const YOU_HAVE_BEEN_KICKED = "you_have_been_kicked";
	const LEADER_LEAVE_YOU_ARE_NEW_LEADER = "leader_leave_you_are_new_leader";
	const YOU_HAVE_BEEN_PROMOTED = "you_have_been_promoted";
	const ONE_USER_LEAVE = "one_user_leave";
	const NEW_INVITATION = "new_invitation";
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="destinataire_user_id", referencedColumnName="id")
     */
    protected $destinataire;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="expediteur_user_id", referencedColumnName="id",nullable=true)
	 */
	protected $expediteur;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="code", type="string",length=255)
	 */
	protected $code;

	/**
	 * @ORM\ManyToOne(targetEntity="Appointment")
	 * @ORM\JoinColumn(name="appointment_id", referencedColumnName="id",nullable=true,onDelete="CASCADE")
	 */
	protected $appointment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="new", type="boolean")
     */
    protected $new;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
	    $this->created = new \DateTime();
    }


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
     * Set created
     *
     * @param \DateTime $created
     * @return Notification
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Notification
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set destinataire
     *
     * @param \Acme\EsBattleBundle\Entity\User $destinataire
     * @return Notification
     */
    public function setDestinataire(\Acme\EsBattleBundle\Entity\User $destinataire = null)
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * Get destinataire
     *
     * @return \Acme\EsBattleBundle\Entity\User 
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * Set expediteur
     *
     * @param \Acme\EsBattleBundle\Entity\User $expediteur
     * @return Notification
     */
    public function setExpediteur(\Acme\EsBattleBundle\Entity\User $expediteur = null)
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    /**
     * Get expediteur
     *
     * @return \Acme\EsBattleBundle\Entity\User 
     */
    public function getExpediteur()
    {
        return $this->expediteur;
    }

    /**
     * Set appointment
     *
     * @param \Acme\EsBattleBundle\Entity\Appointment $appointment
     * @return Notification
     */
    public function setAppointment(\Acme\EsBattleBundle\Entity\Appointment $appointment = null)
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * Get appointment
     *
     * @return \Acme\EsBattleBundle\Entity\Appointment 
     */
    public function getAppointment()
    {
        return $this->appointment;
    }

    /**
     * @return string|\Symfony\Component\Serializer\Encoder\scalar
     */
    public function _toJson(){
        $aNotifications = $this->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($aNotifications, 'json');
    }

    /*
    * Serializes Notification.
    *
    * The serialized data have to contain the fields used by the equals method and the username.
    *
    * @return string
    */
    public function _toArray()
    {
        return array(
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'new' => ($this->getNew() !== false),
            'created_at' => $this->getCreated()->getTimestamp(),
            'expediteur' => $this->getExpediteur()->_toArray(),
            'rdv' => $this->getAppointment()->_toArrayMini()
        );
    }

    /**
     * Set new
     *
     * @param boolean $new
     * @return Notification
     */
    public function setNew($new)
    {
        $this->new = $new;

        return $this;
    }

    /**
     * Get new
     *
     * @return boolean 
     */
    public function getNew()
    {
        return $this->new;
    }
}
