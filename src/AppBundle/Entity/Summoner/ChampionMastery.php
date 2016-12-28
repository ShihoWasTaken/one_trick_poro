<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="champion_mastery")
 */
class ChampionMastery
{
    public function __construct($summonerId, $regionId, $championId)
    {
        $this->summonerId = $summonerId;
        $this->regionId = $regionId;
        $this->championId = $championId;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $summonerId;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $regionId;

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $championId;

    /**
     * @ORM\Column(type="smallint")
     */
    private $level;

    /**
     * @ORM\Column(type="integer")
     */
    private $points;

    /**
     * @ORM\Column(type="boolean")
     */
    private $chestGranted;

    /**
     * @ORM\Column(type="integer")
     */
    private $pointsUntilNextLevel;

    /**
     * @ORM\Column(type="smallint")
     */
    private $tokensEarned;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastPlayTime;
    

    /**
     * Set summonerId
     *
     * @param integer $summonerId
     *
     * @return ChampionMastery
     */
    public function setSummonerId($summonerId)
    {
        $this->summonerId = $summonerId;

        return $this;
    }

    /**
     * Get summonerId
     *
     * @return integer
     */
    public function getSummonerId()
    {
        return $this->summonerId;
    }

    /**
     * Set championId
     *
     * @param integer $championId
     *
     * @return ChampionMastery
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
     * Set level
     *
     * @param integer $level
     *
     * @return ChampionMastery
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
     * Set points
     *
     * @param integer $points
     *
     * @return ChampionMastery
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set chestGranted
     *
     * @param boolean $chestGranted
     *
     * @return ChampionMastery
     */
    public function setChestGranted($chestGranted)
    {
        $this->chestGranted = $chestGranted;

        return $this;
    }

    /**
     * Get chestGranted
     *
     * @return boolean
     */
    public function isChestGranted()
    {
        return $this->chestGranted;
    }

    /**
     * Set pointsUntilNextLevel
     *
     * @param integer $pointsUntilNextLevel
     *
     * @return ChampionMastery
     */
    public function setPointsUntilNextLevel($pointsUntilNextLevel)
    {
        $this->pointsUntilNextLevel = $pointsUntilNextLevel;

        return $this;
    }

    /**
     * Get pointsUntilNextLevel
     *
     * @return integer
     */
    public function getPointsUntilNextLevel()
    {
        return $this->pointsUntilNextLevel;
    }

    /**
     * Set tokensEarned
     *
     * @param integer $tokensEarned
     *
     * @return ChampionMastery
     */
    public function setTokensEarned($tokensEarned)
    {
        $this->tokensEarned = $tokensEarned;

        return $this;
    }

    /**
     * Get tokensEarned
     *
     * @return integer
     */
    public function getTokensEarned()
    {
        return $this->tokensEarned;
    }

    /**
     * Set lastPlayTime
     *
     * @param \DateTime $lastPlayTime
     *
     * @return ChampionMastery
     */
    public function setLastPlayTime($lastPlayTime)
    {
        $this->lastPlayTime = $lastPlayTime;

        return $this;
    }

    /**
     * Get lastPlayTime
     *
     * @return \DateTime
     */
    public function getLastPlayTime()
    {
        return $this->lastPlayTime;
    }
}
