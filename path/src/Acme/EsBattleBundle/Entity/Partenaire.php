<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partenaire
 *
 * @ORM\Table(name="Partenaire")
 * @ORM\Entity
 */
class Partenaire {

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
	 * @ORM\Column(name="nom", type="string", length=255)
	 */
	private $nom;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=255)
	 */
	private $description;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="youtube", type="string", length=255)
	 */
	private $youtube;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="twitch", type="string", length=255)
	 */
	private $twitch;

	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="logo_id", referencedColumnName="id",nullable=true)
	 */
	protected $logo;

	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="tuile_id", referencedColumnName="id",nullable=true)
	 */
	protected $tuile;

	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="header_id", referencedColumnName="id",nullable=true)
	 */
	protected $header;

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
     * Set nom
     *
     * @param string $nom
     * @return Partenaire
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Partenaire
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
     * Set youtube
     *
     * @param string $youtube
     * @return Partenaire
     */
    public function setYoutube($youtube)
    {
        $this->youtube = $youtube;

        return $this;
    }

    /**
     * Get youtube
     *
     * @return string 
     */
    public function getYoutube()
    {
        return $this->youtube;
    }

    /**
     * Set twitch
     *
     * @param string $twitch
     * @return Partenaire
     */
    public function setTwitch($twitch)
    {
        $this->twitch = $twitch;

        return $this;
    }

    /**
     * Get twitch
     *
     * @return string 
     */
    public function getTwitch()
    {
        return $this->twitch;
    }

    /**
     * Set logo
     *
     * @param \Acme\EsBattleBundle\Entity\Document $logo
     * @return Partenaire
     */
    public function setLogo(\Acme\EsBattleBundle\Entity\Document $logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return \Acme\EsBattleBundle\Entity\Document 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set tuile
     *
     * @param \Acme\EsBattleBundle\Entity\Document $tuile
     * @return Partenaire
     */
    public function setTuile(\Acme\EsBattleBundle\Entity\Document $tuile = null)
    {
        $this->tuile = $tuile;

        return $this;
    }

    /**
     * Get tuile
     *
     * @return \Acme\EsBattleBundle\Entity\Document 
     */
    public function getTuile()
    {
        return $this->tuile;
    }

    /**
     * Set header
     *
     * @param \Acme\EsBattleBundle\Entity\Document $header
     * @return Partenaire
     */
    public function setHeader(\Acme\EsBattleBundle\Entity\Document $header = null)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return \Acme\EsBattleBundle\Entity\Document 
     */
    public function getHeader()
    {
        return $this->header;
    }
}
