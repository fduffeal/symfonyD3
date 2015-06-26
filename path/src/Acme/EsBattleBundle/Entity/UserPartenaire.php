<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Class UserGame
 * @package Acme\EsBattleBundle\Entity
 * @ORM\Table(name="user_partenaire")
 * @ORM\Entity
 */
class UserPartenaire {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="userpartenaires")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Partenaire", inversedBy="userpartenaires")
     * @ORM\JoinColumn(name="partenaire_id", referencedColumnName="id")
     */
    protected $partenaire;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="role", type="string", columnDefinition="ENUM('admin', 'normal')")
	 */
	private $role;


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
     * Set role
     *
     * @param string $role
     * @return UserPartenaire
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set user
     *
     * @param \Acme\EsBattleBundle\Entity\User $user
     * @return UserPartenaire
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
     * Set partenaire
     *
     * @param \Acme\EsBattleBundle\Entity\Partenaire $partenaire
     * @return UserPartenaire
     */
    public function setPartenaire(\Acme\EsBattleBundle\Entity\Partenaire $partenaire = null)
    {
        $this->partenaire = $partenaire;

        return $this;
    }

    /**
     * Get partenaire
     *
     * @return \Acme\EsBattleBundle\Entity\Partenaire 
     */
    public function getPartenaire()
    {
        return $this->partenaire;
    }
}
