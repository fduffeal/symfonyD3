<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserAvis
 * @package Acme\EsBattleBundle\Entity
 * @ORM\Table(name="user_avis")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity
 */
class UserAvis {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="mesAvis")
	 * @ORM\JoinColumn(name="auteur_id", referencedColumnName="id")
	 */
	protected $auteur;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="avisSurMoi")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;


	/**
	 * @var string
	 *
	 * @ORM\Column(name="avis", type="text")
	 */
	protected $avis;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;
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
	 * @ORM\PreUpdate
	 */
	public function setUpdatedValue()
	{
		$this->updated = new \DateTime();
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
     * Set avis
     *
     * @param string $avis
     * @return UserAvis
     */
    public function setAvis($avis)
    {
        $this->avis = $avis;

        return $this;
    }

    /**
     * Get avis
     *
     * @return string 
     */
    public function getAvis()
    {
        return $this->avis;
    }

    /**
     * Set auteur
     *
     * @param \Acme\EsBattleBundle\Entity\User $auteur
     * @return UserAvis
     */
    public function setAuteur(\Acme\EsBattleBundle\Entity\User $auteur = null)
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Get auteur
     *
     * @return \Acme\EsBattleBundle\Entity\User 
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set user
     *
     * @param \Acme\EsBattleBundle\Entity\User $user
     * @return UserAvis
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
     * Set created
     *
     * @param \DateTime $created
     * @return UserAvis
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return UserAvis
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

	public function _toArray(){
		return array(
			'id' => $this->getId(),
			'avis' => $this->getAvis(),
			'created' => $this->getCreated(),
			'updated' => $this->getUpdated(),
			'auteur' => $this->getAuteur()->_toArrayShort()
		);
	}
}
