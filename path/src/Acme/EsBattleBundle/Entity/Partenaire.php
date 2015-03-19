<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
	 * @var integer
	 *
	 * @ORM\Column(name="order", type="integer")
	 */
	private $order;

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
	 * @var string
	 *
	 * @ORM\Column(name="facebook", type="string", length=255)
	 */
	private $facebook;


	/**
	 * @var string
	 *
	 * @ORM\Column(name="twitter", type="string", length=255)
	 */
	private $twitter;

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
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="bloc_home_img_id", referencedColumnName="id",nullable=true)
	 */
	protected $blocHomeImg;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="blocHomeLink", type="string", length=255)
	 */
	private $blocHomeLink;


	/**
	 * @ORM\OneToMany(targetEntity="Video",mappedBy="partenaire")
	 */
	protected $videos;


	public function __construct() {
		$this->videos = new ArrayCollection();
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

    /**
     * Add videos
     *
     * @param \Acme\EsBattleBundle\Entity\Video $videos
     * @return Partenaire
     */
    public function addVideo(\Acme\EsBattleBundle\Entity\Video $videos)
    {
        $this->videos[] = $videos;

        return $this;
    }

    /**
     * Remove videos
     *
     * @param \Acme\EsBattleBundle\Entity\Video $videos
     */
    public function removeVideo(\Acme\EsBattleBundle\Entity\Video $videos)
    {
        $this->videos->removeElement($videos);
    }

    /**
     * Get videos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return Partenaire
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string 
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return Partenaire
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string 
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set blocHomeLink
     *
     * @param string $blocHomeLink
     * @return Partenaire
     */
    public function setBlocHomeLink($blocHomeLink)
    {
        $this->blocHomeLink = $blocHomeLink;

        return $this;
    }

    /**
     * Get blocHomeLink
     *
     * @return string 
     */
    public function getBlocHomeLink()
    {
        return $this->blocHomeLink;
    }

    /**
     * Set blocHomeImg
     *
     * @param \Acme\EsBattleBundle\Entity\Document $blocHomeImg
     * @return Partenaire
     */
    public function setBlocHomeImg(\Acme\EsBattleBundle\Entity\Document $blocHomeImg = null)
    {
        $this->blocHomeImg = $blocHomeImg;

        return $this;
    }

    /**
     * Get blocHomeImg
     *
     * @return \Acme\EsBattleBundle\Entity\Document 
     */
    public function getBlocHomeImg()
    {
        return $this->blocHomeImg;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return Partenaire
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

	public function _toArrayShort()
	{
		$blocHomeImg = $this->getBlocHomeImg();
		$logo = $this->getLogo();
		$header = $this->getHeader();
		$tuile = $this->getTuile();

		return array(
			'id' => $this->getId(),
			'nom' => $this->getNom(),
			'description' => $this->getDescription(),
			'youtube' => $this->getYoutube(),
			'twitch' => $this->getTwitch(),
			'facebook' => $this->getFacebook(),
			'twitter' => $this->getTwitter(),
			'blocHomeImg' => ($blocHomeImg)?$blocHomeImg->_toArray():null,
			'blocHomeLink' => $this->getBlocHomeLink(),
			'logo' => ($logo)?$logo->_toArray():null,
			'tuile' => ($tuile)?$tuile->_toArray():null,
			'header' => ($header)?$header->_toArray():null,
		);
	}

	public function _toArray(){
		$videos = $this->getVideos();
		$aVideos = array();
		foreach($videos as $video){
			$aVideos[] = $video->_toArray();
		}

		$array = $this->_toArrayShort();

		$array['videos'] = $aVideos;

		return $array;
	}
}
