<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Topic
 *
 * @ORM\Table(name="Topic",indexes={@ORM\Index(name="updated_idx", columns={"updated"}),@ORM\Index(name="visible_idx", columns={"visible"}),@ORM\Index(name="status_idx", columns={"status"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Topic
{
	const STATUS_HIGH = 'high';
	const STATUS_NORMAL = 'normal';
	const STATUS_POSTIT = 'postit';
	const STATUS_NEWS = 'news';
	const STATUS_VIDEO = 'video';
	const STATUS_TUTO = 'tuto';
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
     * @ORM\Column(name="titre", type="string", length=255)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", columnDefinition="ENUM('postit', 'high','normal','news','video','tuto')")
     */
    private $status;


    /**
     * @ORM\OneToMany(targetEntity="Message",mappedBy="topic")
     */
    protected $messages;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    protected $visible;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="document_id", referencedColumnName="id",nullable=true)
	 */
	protected $document;

	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="vignette_id", referencedColumnName="id",nullable=true)
	 */
	protected $vignette;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbMessages", type="integer")
     */
    protected $nbMessages;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    protected $updated;


    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     */
    public function setPositionValue()
    {
        $this->position = 1;
    }

    /**
     * @ORM\PrePersist
     */
    public function setVisibleValue()
    {
//        $this->visible = true;
    }

    /**
     * @ORM\PrePersist
     */
    public function setStatusValue()
    {
//        $this->status = self::STATUS_NORMAL;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setNbMessageValue()
    {
        $messageVisible = $this->getMessages()->filter(
            function($entry)  {
                return ($entry->getVisible() === true);
            }
        );
        $this->nbMessages = sizeof($messageVisible);
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
        $this->updated = new \DateTime();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
	    $this->visible = true;
		$this->status = self::STATUS_NORMAL;
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
     * Set titre
     *
     * @param string $titre
     * @return Topic
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Topic
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
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Add messages
     *
     * @param \Acme\EsBattleBundle\Entity\Message $messages
     * @return Topic
     */
    public function addMessage(\Acme\EsBattleBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Acme\EsBattleBundle\Entity\Message $messages
     */
    public function removeMessage(\Acme\EsBattleBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set user
     *
     * @param \Acme\EsBattleBundle\Entity\User $user
     * @return Topic
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
     * Set visible
     *
     * @param boolean $visible
     * @return Topic
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Topic
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }


    public function _toJson(){
        $topic = $this->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($topic, 'json');
    }

    public function _toArray(){
        $vignette = $this->getVignette();
        return array(
            'id' => $this->getId(),
            'titre' => $this->getTitre(),
            'created' => $this->getCreated()->getTimestamp(),
            'updated' => $this->getUpdated()->getTimestamp(),
            'user' => $this->getUser()->_toArrayShort(),
            'nbMessages' => $this->getNbMessages(),
            'position' => $this->getPosition(),
            'status' => $this->getStatus(),
            'vignette' => ($vignette)?$vignette->getFullPath():null
        );
    }

    public function _toJsonShort(){
        $topic = $this->_toArrayShort();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($topic, 'json');
    }

    public function _toArrayShort(){
        $vignette = $this->getVignette();

        return array(
            'id' => $this->getId(),
            'titre' => $this->getTitre(),
            'created' => $this->getCreated()->getTimestamp(),
            'updated' => $this->getUpdated()->getTimestamp(),
            'user' => $this->getUser()->_toArrayShort(),
            'nbMessages' => $this->getNbMessages(),
            'position' => $this->getPosition(),
            'status' => $this->getStatus(),
            'vignette' => ($vignette)?$vignette->getFullPath():null
        );
    }

    /**
     * Set nbMessages
     *
     * @param integer $nbMessages
     * @return Topic
     */
    public function setNbMessages($nbMessages)
    {
        $this->nbMessages = $nbMessages;

        return $this;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Topic
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get nbMessages
     *
     * @return integer 
     */
    public function getNbMessages()
    {
        return $this->nbMessages;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Topic
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set document
     *
     * @param \Acme\EsBattleBundle\Entity\Document $document
     * @return Topic
     */
    public function setDocument(\Acme\EsBattleBundle\Entity\Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \Acme\EsBattleBundle\Entity\Document 
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set vignette
     *
     * @param \Acme\EsBattleBundle\Entity\Document $vignette
     * @return Topic
     */
    public function setVignette(\Acme\EsBattleBundle\Entity\Document $vignette = null)
    {
        $this->vignette = $vignette;

        return $this;
    }

    /**
     * Get vignette
     *
     * @return \Acme\EsBattleBundle\Entity\Document 
     */
    public function getVignette()
    {
        return $this->vignette;
    }
}
