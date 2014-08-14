<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User extends  BaseUser
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
     * @ORM\Column(name="login", type="string", length=255)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255)
     */
    private $avatar;


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
     * Set login
     *
     * @param string $login
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
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
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="users")
     * @ORM\JoinColumn(name="groupe_id", referencedColumnName="id")
     */
    protected $groupe;

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
