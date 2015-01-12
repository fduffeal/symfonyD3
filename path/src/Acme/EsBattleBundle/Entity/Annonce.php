<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Appointment
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Annonce
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
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="annonce_tag",
     *      joinColumns={@ORM\JoinColumn(name="annonce_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     **/
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity="UserGame")
     * @ORM\JoinColumn(name="user_game_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="Game")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    protected $game;

    /**
     * @ORM\ManyToOne(targetEntity="Plateform")
     * @ORM\JoinColumn(name="plateform_id", referencedColumnName="id")
     */
    protected $plateform;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Annonce
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
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = new \DateTime();
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Annonce
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
     * Add tags
     *
     * @param \Acme\EsBattleBundle\Entity\Tag $tags
     * @return Annonce
     */
    public function addTag(\Acme\EsBattleBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Acme\EsBattleBundle\Entity\Tag $tags
     */
    public function removeTag(\Acme\EsBattleBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }


    /**
     * Set game
     *
     * @param \Acme\EsBattleBundle\Entity\Game $game
     * @return Annonce
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
     * Set plateform
     *
     * @param \Acme\EsBattleBundle\Entity\Plateform $plateform
     * @return Annonce
     */
    public function setPlateform(\Acme\EsBattleBundle\Entity\Plateform $plateform = null)
    {
        $this->plateform = $plateform;

        return $this;
    }

    /**
     * Get plateform
     *
     * @return \Acme\EsBattleBundle\Entity\Plateform 
     */
    public function getPlateform()
    {
        return $this->plateform;
    }

    /**
     * Set author
     *
     * @param \Acme\EsBattleBundle\Entity\UserGame $author
     * @return Annonce
     */
    public function setAuthor(\Acme\EsBattleBundle\Entity\UserGame $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Acme\EsBattleBundle\Entity\UserGame 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /*
    * Serializes appointment.
    *
    * The serialized data have to contain the fields used by the equals method and the username.
    *
    * @return string
    */
    public function _toArray()
    {

        $tags = $this->getTags();
        $aTags = array();
        foreach($tags as $tag){
            $aTags[] = $tag->_toArray();
        }

        $plateform = $this->getPlateform();
        $game = $this->getGame();

        $author = $this->getAuthor();

        return array(
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'author' => ($author)?$author->_toArray():null,
            'tags' => $aTags,
            'plateform' => ($plateform)?$plateform->_toArray():null,
            'game' => ($game)?$game->_toArray():null,
            'created' => $this->getCreated()->getTimestamp()
        );
    }

    public function _toJson(){
        $aAnnonce = $this->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($aAnnonce, 'json');
    }
}
