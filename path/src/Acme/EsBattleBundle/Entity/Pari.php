<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pari
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Pari
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
     * @ORM\Column(name="video", type="string", length=255)
     */
    private $video;


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
     * @return Pari
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
     * @return Pari
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
     * Set video
     *
     * @param string $video
     * @return Pari
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return string 
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="paris")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    protected $game;

    /**
     * Set game
     *
     * @param \Acme\EsBattleBundle\Entity\Game $game
     * @return Pari
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
     * @ORM\ManyToOne(targetEntity="Evenement", inversedBy="paris")
     * @ORM\JoinColumn(name="evenement_id", referencedColumnName="id")
     */
    protected $evenement;

    /**
     * Set evenement
     *
     * @param \Acme\EsBattleBundle\Entity\Evenement $evenement
     * @return Pari
     */
    public function setEvenement(\Acme\EsBattleBundle\Entity\Evenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement
     *
     * @return \Acme\EsBattleBundle\Entity\Evenement 
     */
    public function getEvenement()
    {
        return $this->evenement;
    }

    /**
     * @ORM\OneToMany(targetEntity="Mise", mappedBy="pari")
     */
    protected $mises;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mises = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add mises
     *
     * @param \Acme\EsBattleBundle\Entity\Mise $mises
     * @return Pari
     */
    public function addMise(\Acme\EsBattleBundle\Entity\Mise $mises)
    {
        $this->mises[] = $mises;

        return $this;
    }

    /**
     * Remove mises
     *
     * @param \Acme\EsBattleBundle\Entity\Mise $mises
     */
    public function removeMise(\Acme\EsBattleBundle\Entity\Mise $mises)
    {
        $this->mises->removeElement($mises);
    }

    /**
     * Get mises
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMises()
    {
        return $this->mises;
    }
}
