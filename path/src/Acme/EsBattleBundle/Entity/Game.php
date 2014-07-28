<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Game
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Game
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
     * @ORM\Column(name="site", type="string", length=255)
     */
    private $site;


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
     * @return Game
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
     * Set site
     *
     * @param string $site
     * @return Game
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return string 
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @ORM\OneToMany(targetEntity="Pari", mappedBy="game")
     */
    protected $paris;

    public function __construct()
    {
        $this->paris = new ArrayCollection();
    }

    /**
     * Add paris
     *
     * @param \Acme\EsBattleBundle\Entity\Pari $paris
     * @return Game
     */
    public function addPari(\Acme\EsBattleBundle\Entity\Pari $paris)
    {
        $this->paris[] = $paris;

        return $this;
    }

    /**
     * Remove paris
     *
     * @param \Acme\EsBattleBundle\Entity\Pari $paris
     */
    public function removePari(\Acme\EsBattleBundle\Entity\Pari $paris)
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
