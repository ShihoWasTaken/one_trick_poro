<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ranked_stats")
 */
class RankedStats
{
    public function __construct($summonerId, $season, $championId)
    {
        $this->summonerId = $summonerId;
        $this->season = $season;
        $this->championId = $championId;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Summoner")
     */
    private $summonerId;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $season;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Champion")
     */
    private $championId;

    /**
     * @ORM\Column(name="winrate", type="decimal", precision=4, scale=2)
     */
    private $winrate;

    /**
     * @ORM\Column(name="playedGames", type="smallint")
     */
    private $playedGames;

    /**
     * @ORM\Column(name="wins", type="smallint")
     */
    private $wins;

    /**
     * @ORM\Column(name="loses", type="smallint")
     */
    private $loses;

    /**
     * @ORM\Column(name="kills", type="integer")
     */
    private $kills;

    /**
     * @ORM\Column(name="deaths", type="integer")
     */
    private $deaths;

    /**
     * @ORM\Column(name="assists", type="integer")
     */
    private $assists;

    /**
     * @ORM\Column(name="creeps", type="smallint")
     */
    private $creeps;

    /**
     * Set summonerId
     *
     * @param integer $summonerId
     *
     * @return RankedStats
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
     * Set season
     *
     * @param integer $season
     *
     * @return RankedStats
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return integer
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set championId
     *
     * @param integer $championId
     *
     * @return RankedStats
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
     * Set winrate
     *
     * @param integer $winrate
     *
     * @return RankedStats
     */
    public function setWinrate($winrate)
    {
        $this->winrate = $winrate;

        return $this;
    }

    /**
     * Get winrate
     *
     * @return integer
     */
    public function getWinrate()
    {
        return $this->winrate;
    }

    /**
     * Set playedGames
     *
     * @param integer $playedGames
     *
     * @return RankedStats
     */
    public function setPlayedGames($playedGames)
    {
        $this->playedGames = $playedGames;

        return $this;
    }

    /**
     * Get playedGames
     *
     * @return integer
     */
    public function getPlayedGames()
    {
        return $this->playedGames;
    }

    /**
     * Set wins
     *
     * @param integer $wins
     *
     * @return RankedStats
     */
    public function setWins($wins)
    {
        $this->wins = $wins;

        return $this;
    }

    /**
     * Get wins
     *
     * @return integer
     */
    public function getWins()
    {
        return $this->wins;
    }

    /**
     * Set loses
     *
     * @param integer $loses
     *
     * @return RankedStats
     */
    public function setLoses($loses)
    {
        $this->loses = $loses;

        return $this;
    }

    /**
     * Get loses
     *
     * @return integer
     */
    public function getLoses()
    {
        return $this->loses;
    }

    /**
     * Set kills
     *
     * @param integer $kills
     *
     * @return RankedStats
     */
    public function setKills($kills)
    {
        $this->kills = $kills;

        return $this;
    }

    /**
     * Get kills
     *
     * @return integer
     */
    public function getKills()
    {
        return $this->kills;
    }

    /**
     * Set deaths
     *
     * @param integer $deaths
     *
     * @return RankedStats
     */
    public function setDeaths($deaths)
    {
        $this->deaths = $deaths;

        return $this;
    }

    /**
     * Get deaths
     *
     * @return integer
     */
    public function getDeaths()
    {
        return $this->deaths;
    }

    /**
     * Set assists
     *
     * @param integer $assists
     *
     * @return RankedStats
     */
    public function setAssists($assists)
    {
        $this->assists = $assists;

        return $this;
    }

    /**
     * Get assists
     *
     * @return integer
     */
    public function getAssists()
    {
        return $this->assists;
    }

    /**
     * Set creeps
     *
     * @param integer $creeps
     *
     * @return RankedStats
     */
    public function setCreeps($creeps)
    {
        $this->creeps = $creeps;

        return $this;
    }

    /**
     * Get creeps
     *
     * @return integer
     */
    public function getCreeps()
    {
        return $this->creeps;
    }
}
