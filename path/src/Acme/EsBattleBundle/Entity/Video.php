<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Video
 *
 * @ORM\Table(name="Video",indexes={@ORM\Index(name="created_idx", columns={"created"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Video
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
	 * @ORM\Column(name="url", type="string", length=255)
	 */
	private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="video_tag",
     *      joinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     **/
    private $tags;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;

	/**
	 * @ORM\ManyToOne(targetEntity="Partenaire", inversedBy="videos")
	 * @ORM\JoinColumn(name="partenaire_id", referencedColumnName="id")
	 **/
	private $product;

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
     * @return Video
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
     * Set created
     *
     * @param \DateTime $created
     * @return Video
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
	 * @ORM\PrePersist
	 */
	public function setCreatedValue()
	{
		$this->created = new \DateTime();
	}

    /**
     * Add tags
     *
     * @param \Acme\EsBattleBundle\Entity\Tag $tags
     * @return Video
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
     * Set url
     *
     * @param string $url
     * @return Video
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
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


		$created = $this->getCreated();

		return array(
			'id' => $this->getId(),
			'url' => $this->getUrl(),
			'description' => $this->getDescription(),
			'tags' => $aTags,
			'created' => ($created)?$created->getTimestamp():null
		);
	}

	public function _toJson(){
		$aVideo = $this->_toArray();

		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());

		$serializer = new Serializer($normalizers, $encoders);

		return $serializer->serialize($aVideo, 'json');
	}

    /**
     * Set product
     *
     * @param \Acme\EsBattleBundle\Entity\Partenaire $product
     * @return Video
     */
    public function setProduct(\Acme\EsBattleBundle\Entity\Partenaire $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Acme\EsBattleBundle\Entity\Partenaire 
     */
    public function getProduct()
    {
        return $this->product;
    }
}
