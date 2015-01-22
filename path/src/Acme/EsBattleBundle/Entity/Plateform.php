<?php
/**
 * Created by PhpStorm.
 * User: francisduffeal
 * Date: 04/11/14
 * Time: 21:12
 */

namespace Acme\EsBattleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Plateform
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Plateform {

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
     * @ORM\Column(name="nom", type="string",length=255)
     */
    private $nom;

    /**
     * @var int
     * @ORM\Column(name="bungiePlateformId", type="integer")
     */
    private $bungiePlateformId;

    /**
     * @ORM\OneToMany(targetEntity="UserGame",mappedBy="plateform")
     */
    protected $usergames;

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
     * Set nom
     *
     * @param string $nom
     * @return Plateform
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    public function _toArray(){
        return array(
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'bungiePlateformId' => $this->getBungiePlateformId()
        );
    }

    public function _toJson(){
        $plateform = $this->_toArray();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($plateform, 'json');
    }

    /**
     * Set bungiePlateformId
     *
     * @param integer $bungiePlateformId
     * @return Plateform
     */
    public function setBungiePlateformId($bungiePlateformId)
    {
        $this->bungiePlateformId = $bungiePlateformId;

        return $this;
    }

    /**
     * Get bungiePlateformId
     *
     * @return integer 
     */
    public function getBungiePlateformId()
    {
        return $this->bungiePlateformId;
    }
}
