<?php

namespace AppBundle\Entity\StaticData;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mastery")
 */
class Mastery
{
    const ERROR = 0;
    const FEROCITY = 1;
    const CUNNING = 2;
    const RESOLVE = 3;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @ORM\Column(name="maxRank", type="smallint")
     */
    private $maxRank;

    /**
     * @ORM\Column(name="masteryTree", type="smallint")
     */
    private $masteryTree;

    /**
     * @ORM\Column(name="image", type="string", length=32)
     */
    private $image;

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Mastery
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set image
     *
     * @param string $image
     *
     * @return Mastery
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set masteryTree
     *
     * @param integer $masteryTree
     *
     * @return Mastery
     */
    public function setMasteryTree($masteryTree)
    {
        switch($masteryTree)
        {
            // En cas d'erreur
            default:
                $this->masteryTree = self::ERROR;
                break;
            // Férocité - Rouge
            case 'Ferocity':
                $this->masteryTree = self::FEROCITY;
                break;
            // Ingéniosité - Bleu
            case 'Cunning':
                $this->masteryTree = self::CUNNING;
                break;
            // Volonté - Vert
            case 'Resolve':
                $this->masteryTree = self::RESOLVE;
                break;
        }

        return $this;
    }

    /**
     * Get masteryTree
     *
     * @return integer
     */
    public function getMasteryTree()
    {
        return $this->masteryTree;
    }

    /**
     * Set maxRank
     *
     * @param integer $maxRank
     *
     * @return Mastery
     */
    public function setMaxRank($maxRank)
    {
        $this->maxRank = $maxRank;

        return $this;
    }

    /**
     * Get maxRank
     *
     * @return integer
     */
    public function getMaxRank()
    {
        return $this->maxRank;
    }
}
