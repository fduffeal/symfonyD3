<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * @package Acme\EsBattleBundle\Entity
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Matchmaking
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
	 * @var string
	 *
	 * @ORM\Column(name="icone", type="string", length=255)
	 */
	private $icone;

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
	 * @ORM\JoinTable(name="matchmaking_tag",
	 *      joinColumns={@ORM\JoinColumn(name="matchmaking_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
	 *      )
	 **/
	private $tags;

	/**
	 * @ORM\ManyToOne(targetEntity="Game", inversedBy="games")
	 * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
	 */
	protected $game;

	/**
	 * @ORM\ManyToMany(targetEntity="Plateform")
	 * @ORM\JoinTable(name="matchmaking_plateform",
	 *      joinColumns={@ORM\JoinColumn(name="matchmaking_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="plateform_id", referencedColumnName="id")}
	 *      )
	 **/
	protected $plateforms;


	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="vignette_id", referencedColumnName="id",nullable=true)
	 */
	protected $vignette;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		$this->plateforms = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set description
     *
     * @param string $description
     * @return Matchmaking
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
     * Set duree
     *
     * @param integer $duree
     * @return Matchmaking
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
     * Set nbParticipant
     *
     * @param integer $nbParticipant
     * @return Matchmaking
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
     * Add tags
     *
     * @param \Acme\EsBattleBundle\Entity\Tag $tags
     * @return Matchmaking
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
     * Set game
     *
     * @param \Acme\EsBattleBundle\Entity\Game $game
     * @return Matchmaking
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
     * Add plateforms
     *
     * @param \Acme\EsBattleBundle\Entity\Plateform $plateforms
     * @return Matchmaking
     */
    public function addPlateform(\Acme\EsBattleBundle\Entity\Plateform $plateforms)
    {
        $this->plateforms[] = $plateforms;

        return $this;
    }

    /**
     * Remove plateforms
     *
     * @param \Acme\EsBattleBundle\Entity\Plateform $plateforms
     */
    public function removePlateform(\Acme\EsBattleBundle\Entity\Plateform $plateforms)
    {
        $this->plateforms->removeElement($plateforms);
    }

    /**
     * Get plateforms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlateforms()
    {
        return $this->plateforms;
    }

    /**
     * Set icone
     *
     * @param string $icone
     * @return Matchmaking
     */
    public function setIcone($icone)
    {
        $this->icone = $icone;

        return $this;
    }

    /**
     * Get icone
     *
     * @return string 
     */
    public function getIcone()
    {
        return $this->icone;
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
		$aTag = array();
		$tagsCollection = $this->getTags();
		foreach($tagsCollection as $key => $tag){
			$aTag[$key] = $tag->_toArray();
		}

		$aPlateform = array();
		$plateformsCollection = $this->getPlateforms();
		foreach($plateformsCollection as $key => $plateform){
			$aPlateform[$key] = $plateform->_toArray();
		}


		$vignette = $this->getVignette();
		return array(
			'id' => $this->getId(),
			'description' => $this->getDescription(),
			'duree' => $this->getDuree(),
			'game' => $this->getGame()->_toArray(),
			'icone' => $this->getIcone(),
			'nbParticipant' => $this->getNbParticipant(),
			'tags'=> $aTag,
			'plateforms' => $aPlateform,
			'vignette' => ($vignette)?$vignette->_toArray():null
		);
	}

    /**
     * Set vignette
     *
     * @param \Acme\EsBattleBundle\Entity\Document $vignette
     * @return Matchmaking
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
