<?php

namespace AppBundle\Entity\StaticData;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="summoner_spell")
 */
class SummonerSpell
{
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
     * @ORM\Column(name="key", type="string", length=32))
     */
    private $key;

    /**
     * @ORM\Column(name="range", type="smallint")
     */
    private $range;

    /**
     * @ORM\Column(name="cooldown", type="smallint")
     */
    private $cooldown;

    /**
     * @ORM\Column(name="level", type="smallint")
     */
    private $level;

    /**
     * @ORM\Column(name="image", type="string", length=32))
     */
    private $image;

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return SummonerSpell
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
     * Set key
     *
     * @param string $key
     *
     * @return SummonerSpell
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set range
     *
     * @param integer $range
     *
     * @return SummonerSpell
     */
    public function setRange($range)
    {
        $this->range = $range;

        return $this;
    }

    /**
     * Get range
     *
     * @return integer
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Set cooldown
     *
     * @param integer $cooldown
     *
     * @return SummonerSpell
     */
    public function setCooldown($cooldown)
    {
        $this->cooldown = $cooldown;

        return $this;
    }

    /**
     * Get cooldown
     *
     * @return integer
     */
    public function getCooldown()
    {
        return $this->cooldown;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return SummonerSpell
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return SummonerSpell
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
