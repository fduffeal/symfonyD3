<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mise
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Mise
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
     * @var float
     *
     * @ORM\Column(name="valeur", type="float")
     */
    private $valeur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * Set valeur
     *
     * @param float $valeur
     * @return Mise
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return float 
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Mise
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Pari", inversedBy="mises")
     * @ORM\JoinColumn(name="pari_id", referencedColumnName="id")
     */
    protected $pari;

    /**
     * Set pari
     *
     * @param \Acme\EsBattleBundle\Entity\Pari $pari
     * @return Mise
     */
    public function setPari(\Acme\EsBattleBundle\Entity\Pari $pari = null)
    {
        $this->pari = $pari;

        return $this;
    }

    /**
     * Get pari
     *
     * @return \Acme\EsBattleBundle\Entity\Pari 
     */
    public function getPari()
    {
        return $this->pari;
    }
}
