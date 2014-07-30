<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Evenement
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Evenement
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

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
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime")
     */
    private $end;


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
     * Set name
     *
     * @param string $name
     * @return Evenement
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
     * Set end
     *
     * @param \DateTime $end
     * @return Evenement
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

    /**
     * @ORM\OneToMany(targetEntity="Paris", mappedBy="evenement")
     */
    protected $paris;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->paris = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add paris
     *
     * @param \Acme\EsBattleBundle\Entity\Paris $paris
     * @return Evenement
     */
    public function addPari(\Acme\EsBattleBundle\Entity\Paris $paris)
    {
        $this->paris[] = $paris;

        return $this;
    }

    /**
     * Remove paris
     *
     * @param \Acme\EsBattleBundle\Entity\Paris $paris
     */
    public function removePari(\Acme\EsBattleBundle\Entity\Paris $paris)
    {
        $this->paris->removeElement($paris);
    }

    /**
     * Get paris
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParis()
    {
        return $this->paris;
    }
}
