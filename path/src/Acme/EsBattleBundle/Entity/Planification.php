<?php

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;

/**
 * Video
 *
 * @ORM\Table(name="Planification")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Planification
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
	 * @ORM\Column(name="titre", type="string", length=255)
	 */
	private $titre;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=255)
	 */
	private $description;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="start", type="datetime")
	 */
	private $start;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="end", type="datetime")
	 */
	private $end;

	/**
	 * @ORM\ManyToOne(targetEntity="Video")
	 * @ORM\JoinColumn(name="video_id", referencedColumnName="id")
	 **/
	private $video;

	/**
	 * @ORM\ManyToOne(targetEntity="Document")
	 * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
	 **/
	private $image;

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
     * Set titre
     *
     * @param string $titre
     * @return Planification
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Planification
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
     * Set start
     *
     * @param \DateTime $start
     * @return Planification
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Planification
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set video
     *
     * @param \Acme\EsBattleBundle\Entity\Video $video
     * @return Planification
     */
    public function setVideo(\Acme\EsBattleBundle\Entity\Video $video = null)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return \Acme\EsBattleBundle\Entity\Video 
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set image
     *
     * @param \Acme\EsBattleBundle\Entity\Document $image
     * @return Planification
     */
    public function setImage(\Acme\EsBattleBundle\Entity\Document $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Acme\EsBattleBundle\Entity\Document 
     */
    public function getImage()
    {
        return $this->image;
    }

	public function _toArray(){
		$image = $this->getImage();
		$video = $this->getVideo();
		return array(
			'id'=> $this->getId(),
			'titre'=>$this->getTitre(),
			'description'=>$this->getDescription(),
			'start'=>($this->getStart())?$this->getStart()->getTimestamp():null,
			'end'=>($this->getEnd())?$this->getEnd()->getTimestamp():null,
			'image'=>($image)?$image->_toArray():null,
			'video'=>($video)?$video->_toArray():null,
		);
	}
}
