<?php

namespace AppBundle\Entity\StaticData;

use Symfony\Component\Validator\Constraints as Assert;
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
}