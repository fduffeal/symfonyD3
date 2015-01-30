<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Class Stat
 * @package Acme\EsBattleBundle\Entity
 * @ORM\Table(name="stat")
 * @ORM\Entity
 */
class Stat {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="UserStats",mappedBy="stat")
     */
    protected $userstats;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userstats = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Stat
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
     * Add userstats
     *
     * @param \Acme\EsBattleBundle\Entity\UserStats $userstats
     * @return Stat
     */
    public function addUserstat(\Acme\EsBattleBundle\Entity\UserStats $userstats)
    {
        $this->userstats[] = $userstats;

        return $this;
    }

    /**
     * Remove userstats
     *
     * @param \Acme\EsBattleBundle\Entity\UserStats $userstats
     */
    public function removeUserstat(\Acme\EsBattleBundle\Entity\UserStats $userstats)
    {
        $this->userstats->removeElement($userstats);
    }

    /**
     * Get userstats
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserstats()
    {
        return $this->userstats;
    }
}
