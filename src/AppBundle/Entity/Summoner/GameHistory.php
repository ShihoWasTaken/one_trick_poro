<?php

namespace AppBundle\Entity\Summoner;

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

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return GameHistory
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
     * Set championId
     *
     * @param integer $championId
     *
     * @return GameHistory
     */
    public function setChampionId($championId)
    {
        $this->championId = $championId;

        return $this;
    }

    /**
     * Get championId
     *
     * @return integer
     */
    public function getChampionId()
    {
        return $this->championId;
    }

    /**
     * Set spellId1
     *
     * @param integer $spellId1
     *
     * @return GameHistory
     */
    public function setSpellId1($spellId1)
    {
        $this->spellId1 = $spellId1;

        return $this;
    }

    /**
     * Get spellId1
     *
     * @return integer
     */
    public function getSpellId1()
    {
        return $this->spellId1;
    }

    /**
     * Set spellId2
     *
     * @param integer $spellId2
     *
     * @return GameHistory
     */
    public function setSpellId2($spellId2)
    {
        $this->spellId2 = $spellId2;

        return $this;
    }

    /**
     * Get spellId2
     *
     * @return integer
     */
    public function getSpellId2()
    {
        return $this->spellId2;
    }

    /**
     * Set isWin
     *
     * @param boolean $isWin
     *
     * @return GameHistory
     */
    public function setIsWin($isWin)
    {
        $this->isWin = $isWin;

        return $this;
    }

    /**
     * Get isWin
     *
     * @return boolean
     */
    public function getIsWin()
    {
        return $this->isWin;
    }
}
