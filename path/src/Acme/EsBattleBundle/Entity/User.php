<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
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
	 * @var string
	 *
	 * @ORM\Column(name="username", type="string", length=255)
	 */
	private  $username;


	/**
	 * @var string
	 *
	 * @ORM\Column(name="email", type="string", length=255)
	 */
	private  $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="password", type="string", length=255)
	 */
	private  $password;


	/**
	 * @var string
	 *
	 * @ORM\Column(name="salt", type="string", length=255)
	 */
	private  $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="apikey", type="string", length=255)
     */
    private  $apikey;

    /**
     * @var string
     *
     * @ORM\Column(name="psn", type="string", length=255,nullable=true)
     */
    private  $psn;

    /**
     * @var string
     *
     * @ORM\Column(name="xbox", type="string", length=255,nullable=true)
     */
    private  $xbox;


    /**
     * @ORM\ManyToMany(targetEntity="Clan")
     * @ORM\JoinTable(name="users_clans",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="clan_id", referencedColumnName="id")}
     *      )
     **/
    private $clans;

    public function __construct() {
	    $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
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

	/**
	 * Serializes the user.
	 *
	 * The serialized data have to contain the fields used by the equals method and the username.
	 *
	 * @return string
	 */
	public function _toArray()
	{
		return array(
            'id' => $this->getId(),
			'username' => $this->getUsername()
		);
	}

    public function _toJson(){
        $aUser = $this->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($aUser, 'json');
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
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
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

	public function makePassword($password){
		return base_convert(sha1($password.$this->salt), 16, 36);
	}

    public function createApiKey(){
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36).base_convert(sha1(uniqid(mt_rand(), true).$this->username.$this->id), 16, 36);
    }

	public function isPasswordOk($password){
		return $this->password === $this->makePassword($password);
	}

    /**
     * Set apikey
     *
     * @param string $apikey
     * @return User
     */
    public function setApikey($apikey)
    {
        $this->apikey = $apikey;

        return $this;
    }

    /**
     * Get apikey
     *
     * @return string 
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * Set psn
     *
     * @param string $psn
     * @return User
     */
    public function setPsn($psn)
    {
        $this->psn = $psn;

        return $this;
    }

    /**
     * Get psn
     *
     * @return string 
     */
    public function getPsn()
    {
        return $this->psn;
    }

    /**
     * Set xbox
     *
     * @param string $xbox
     * @return User
     */
    public function setXbox($xbox)
    {
        $this->xbox = $xbox;

        return $this;
    }

    /**
     * Get xbox
     *
     * @return string 
     */
    public function getXbox()
    {
        return $this->xbox;
    }
}
