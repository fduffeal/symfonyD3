<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User extends BaseUser
{
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
     * @ORM\Column(name="avatar", type="string", length=255,nullable=true)
     */
    private  $avatar;


    /**
     * @ORM\ManyToMany(targetEntity="Clan")
     * @ORM\JoinTable(name="users_clans",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="clan_id", referencedColumnName="id")}
     *      )
     **/
    private $clans;

    public function __construct() {

        parent::__construct();
        // your own logic

        $this->clans = new ArrayCollection();
    }


    /**
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="users")
     * @ORM\JoinColumn(name="groupe_id", referencedColumnName="id")
     */
    protected $groupe;


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
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Add clans
     *
     * @param \Acme\EsBattleBundle\Entity\Clan $clans
     * @return User
     */
    public function addClan(\Acme\EsBattleBundle\Entity\Clan $clans)
    {
        $this->clans[] = $clans;

        return $this;
    }

    /**
     * Remove clans
     *
     * @param \Acme\EsBattleBundle\Entity\Clan $clans
     */
    public function removeClan(\Acme\EsBattleBundle\Entity\Clan $clans)
    {
        $this->clans->removeElement($clans);
    }

    /**
     * Get clans
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClans()
    {
        return $this->clans;
    }

    /**
     * Set groupe
     *
     * @param \Acme\EsBattleBundle\Entity\Groupe $groupe
     * @return User
     */
    public function setGroupe(\Acme\EsBattleBundle\Entity\Groupe $groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \Acme\EsBattleBundle\Entity\Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }
}
