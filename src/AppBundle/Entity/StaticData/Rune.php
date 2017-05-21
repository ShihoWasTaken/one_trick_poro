<?php

namespace AppBundle\Entity\StaticData;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StaticData\RuneRepository")
 * @ORM\Table(name="rune")
 */
class Rune
{
    const ERROR = 0;
    const RED = 1;
    const YELLOW = 2;
    const BLUE = 3;
    const BLACK = 4;

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
     * @ORM\Column(type="smallint")
     */
    private $tier;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $image;


    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Rune
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
     * Set tier
     *
     * @param integer $tier
     *
     * @return Rune
     */
    public function setTier($tier)
    {
        $this->tier = $tier;

        return $this;
    }

    /**
     * Get tier
     *
     * @return integer
     */
    public function getTier()
    {
        return $this->tier;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Rune
     */
    public function setType($type)
    {
        switch ($type) {
            // En cas d'erreur
            default:
                $this->masteryTree = self::ERROR;
                break;
            // Rouge - Marque
            case 'red':
                $this->type = self::RED;
                break;
            // Jaune - Sceau
            case 'yellow':
                $this->type = self::YELLOW;
                break;
            // Bleu - Glyphe
            case 'blue':
                $this->type = self::BLUE;
                break;
            // Noir - Quintessence
            case 'black':
                $this->type = self::BLACK;
                break;
        }

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Rune
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
}
