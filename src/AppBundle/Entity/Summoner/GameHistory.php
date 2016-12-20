<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="game_history")
 */
class GameHistory
{
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @ORM\Column(name="championId", type="integer")
     * @ORM\OneToOne(targetEntity="Champion")
     */
    private $championId;

    /**
     * @ORM\Column(name="spellId1", type="integer")
     * @ORM\OneToOne(targetEntity="SummonerSpell")
     */
    private $spellId1;

    /**
     * @ORM\Column(name="spellId2", type="integer")
     * @ORM\OneToOne(targetEntity="SummonerSpell")
     */
    private $spellId2;

    /**
     * @ORM\Column(name="isWin", type="boolean")
     */
    private $isWin;
}