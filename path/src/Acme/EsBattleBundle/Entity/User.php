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
 * @ORM\Table(name="user",indexes={@ORM\Index(name="login_idx", columns={"username","apikey"}),@ORM\Index(name="created_idx", columns={"created"}),@ORM\Index(name="onlineTime_idx", columns={"onlineTime"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class User
{
	const ROLE_MODO = 'modo';
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
     * @ORM\Column(name="forgetKey", type="string", length=255,nullable=true)
     */
    private  $forgetKey;

    /**
     * @var string
     *
     * @ORM\Column(name="forgetTime", type="datetime",nullable=true)
     */
    private  $forgetTime;

    /**
     * @var string
     *
     * @ORM\Column(name="onlineTime", type="datetime",nullable=true)
     */
    private  $onlineTime;

    /**
     * @ORM\ManyToMany(targetEntity="Clan")
     * @ORM\JoinTable(name="users_clans",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="clan_id", referencedColumnName="id")}
     *      )
     **/
    private $clans;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string",nullable=true)
     */
    protected  $role;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="UserGame",mappedBy="user")
     */
    protected $usergames;

    /**
     * @ORM\OneToMany(targetEntity="UserStats",mappedBy="stat")
     */
    protected $userstats;

    public function __construct() {
	    $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->clans = new ArrayCollection();
        $this->friends = new ArrayCollection();
        $this->friendsWithMe = new ArrayCollection();
        $this->hasBlacklistedMe = new ArrayCollection();
        $this->blacklistedUser = new ArrayCollection();
    }


    /**
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="users")
     * @ORM\JoinColumn(name="groupe_id", referencedColumnName="id")
     */
    protected $groupe;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="friends")
     **/
    private $friendsWithMe;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="friendsWithMe")
     * @ORM\JoinTable(name="users_friends",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="friend_user_id", referencedColumnName="id")}
     *      )
     **/
    protected $friends;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="blacklistedUser")
     **/
    private $hasBlacklistedMe;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="hasBlacklistedMe")
     * @ORM\JoinTable(name="users_blacklist",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="blacklisted_user_id", referencedColumnName="id")}
     *      )
     **/
    protected $blacklistedUser;

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
        $aUserGameCollection = array();

        $userGameCollection = $this->getUsergames();
		if($userGameCollection !== null){
			foreach($userGameCollection as $key => $userGame){
				$aUserGameCollection[$key] = $userGame->_toArray();
			}
		}

        $online = $this->getOnlineTime();

		return array(
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'userGame'=> $aUserGameCollection,
            'onlineTime' => ($online!==null)?$this->getOnlineTime()->getTimestamp():'',
            'role' => $this->getRole()
		);
	}

    public function _toArrayShort(){
        return array(
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'role' => $this->getRole()
        );
    }

    /**
     * Serializes the user private.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function _toArrayPrivate()
    {

        $aUserPublic = $this->_toArray();

        $aUserPublic['token'] = $this->getApikey();
        $aUserPublic['email'] = $this->getEmail();

        return $aUserPublic;
    }

    public function _toJson(){
        $aUser = $this->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($aUser, 'json');
    }

    public function _toJsonPrivate(){
        $aUser = $this->_toArrayPrivate();

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
     * Set forgetKey
     *
     * @param string $forgetKey
     * @return User
     */
    public function setForgetKey($forgetKey)
    {
        $this->forgetKey = $forgetKey;

        return $this;
    }

    /**
     * Get forgetKey
     *
     * @return string 
     */
    public function getForgetKey()
    {
        return $this->forgetKey;
    }

    /**
     * Set forgetTime
     *
     * @param \DateTime $forgetTime
     * @return User
     */
    public function setForgetTime($forgetTime)
    {
        $this->forgetTime = $forgetTime;

        return $this;
    }

    /**
     * Get forgetTime
     *
     * @return \DateTime 
     */
    public function getForgetTime()
    {
        return $this->forgetTime;
    }

    /**
     * Add usergames
     *
     * @param \Acme\EsBattleBundle\Entity\UserGame $usergames
     * @return User
     */
    public function addUsergame(\Acme\EsBattleBundle\Entity\UserGame $usergames)
    {
        $this->usergames[] = $usergames;

        return $this;
    }

    /**
     * Remove usergames
     *
     * @param \Acme\EsBattleBundle\Entity\UserGame $usergames
     */
    public function removeUsergame(\Acme\EsBattleBundle\Entity\UserGame $usergames)
    {
        $this->usergames->removeElement($usergames);
    }

    /**
     * Get usergames
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsergames()
    {
        return $this->usergames;
    }

    /**
     * Set onlineTime
     *
     * @param \DateTime $onlineTime
     * @return User
     */
    public function setOnlineTime($onlineTime)
    {
        $this->onlineTime = $onlineTime;

        return $this;
    }

    /**
     * Get onlineTime
     *
     * @return \DateTime 
     */
    public function getOnlineTime()
    {
        return $this->onlineTime;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setOnlineTimeValue()
    {
        $this->onlineTime = new \DateTime();
    }

    /**
     * Add friends
     *
     * @param \Acme\EsBattleBundle\Entity\User $friends
     * @return User
     */
    public function addFriend(\Acme\EsBattleBundle\Entity\User $friends)
    {
        $this->friends[] = $friends;

        return $this;
    }

    /**
     * Remove friends
     *
     * @param \Acme\EsBattleBundle\Entity\User $friends
     */
    public function removeFriend(\Acme\EsBattleBundle\Entity\User $friends)
    {
        $this->friends->removeElement($friends);
    }

    /**
     * Get friends
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * Add friendsWithMe
     *
     * @param \Acme\EsBattleBundle\Entity\User $friendsWithMe
     * @return User
     */
    public function addFriendsWithMe(\Acme\EsBattleBundle\Entity\User $friendsWithMe)
    {
        $this->friendsWithMe[] = $friendsWithMe;

        return $this;
    }

    /**
     * Remove friendsWithMe
     *
     * @param \Acme\EsBattleBundle\Entity\User $friendsWithMe
     */
    public function removeFriendsWithMe(\Acme\EsBattleBundle\Entity\User $friendsWithMe)
    {
        $this->friendsWithMe->removeElement($friendsWithMe);
    }

    /**
     * Get friendsWithMe
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFriendsWithMe()
    {
        return $this->friendsWithMe;
    }

    /**
     * Add hasBlacklistedMe
     *
     * @param \Acme\EsBattleBundle\Entity\User $hasBlacklistedMe
     * @return User
     */
    public function addHasBlacklistedMe(\Acme\EsBattleBundle\Entity\User $hasBlacklistedMe)
    {
        $this->hasBlacklistedMe[] = $hasBlacklistedMe;

        return $this;
    }

    /**
     * Remove hasBlacklistedMe
     *
     * @param \Acme\EsBattleBundle\Entity\User $hasBlacklistedMe
     */
    public function removeHasBlacklistedMe(\Acme\EsBattleBundle\Entity\User $hasBlacklistedMe)
    {
        $this->hasBlacklistedMe->removeElement($hasBlacklistedMe);
    }

    /**
     * Get hasBlacklistedMe
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHasBlacklistedMe()
    {
        return $this->hasBlacklistedMe;
    }

    /**
     * Add blacklistedUser
     *
     * @param \Acme\EsBattleBundle\Entity\User $blacklistedUser
     * @return User
     */
    public function addBlacklistedUser(\Acme\EsBattleBundle\Entity\User $blacklistedUser)
    {
        $this->blacklistedUser[] = $blacklistedUser;

        return $this;
    }

    /**
     * Remove blacklistedUser
     *
     * @param \Acme\EsBattleBundle\Entity\User $blacklistedUser
     */
    public function removeBlacklistedUser(\Acme\EsBattleBundle\Entity\User $blacklistedUser)
    {
        $this->blacklistedUser->removeElement($blacklistedUser);
    }

    /**
     * Get blacklistedUser
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBlacklistedUser()
    {
        return $this->blacklistedUser;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = new \DateTime();
    }

    /**
     * Set role
     *
     * @param string $role
     * @return User
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
     * Set created
     *
     * @param \DateTime $created
     * @return User
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Add userstats
     *
     * @param \Acme\EsBattleBundle\Entity\UserStats $userstats
     * @return User
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

	public function isModo(){
        return preg_match('/'.self::ROLE_MODO.'/',$this->getRole());
	}
}
