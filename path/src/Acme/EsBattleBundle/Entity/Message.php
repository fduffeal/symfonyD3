<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Message
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Message
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
     * @var text
     *
     * @ORM\Column(name="texte", type="text")
     */
    protected $texte;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;

    /**
     * @ORM\ManyToOne(targetEntity="Topic")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="id")
     */
    protected $topic;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    protected $visible;

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
    public function setVisibleValue()
    {
        $this->visible = true;
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
     * Set texte
     *
     * @param string $texte
     * @return Message
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte
     *
     * @return string 
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Message
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
     * Set topic
     *
     * @param \Acme\EsBattleBundle\Entity\Topic $topic
     * @return Message
     */
    public function setTopic(\Acme\EsBattleBundle\Entity\Topic $topic = null)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return \Acme\EsBattleBundle\Entity\Topic 
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set user
     *
     * @param \Acme\EsBattleBundle\Entity\User $user
     * @return Message
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
     * @return Message
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }
}
