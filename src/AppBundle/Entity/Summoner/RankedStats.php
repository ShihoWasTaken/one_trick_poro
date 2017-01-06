<?php

namespace AppBundle\Entity\Summoner;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ranked_stats")
 */
class RankedStats
{
    public function __construct($summonerId, $regionId, $season, $championId)
    {
        $this->summonerId = $summonerId;
        $this->regionId = $regionId;
        $this->season = $season;
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
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $regionId;

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
     */
    private $championId;

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
     * @ORM\Column(name="creeps", type="integer")
     */
    private $creeps;

    /**
     * Get winrate
     *
     * @return float
     */
    public function getWinrate()
    {
        return round(($this->wins / $this->getPlayedGames() * 100), 2);
    }

    /**
     * Get KDA
     *
     * @return float
     */
    public function getKDA()
    {
         return round((($this->kills +  $this->assists) / max(1, $this->deaths)) , 2);
    }

    /**
     * Get KDA
     *
     * @return float
     */
    public function getKillsAVG()
    {
        return round(($this->kills / $this->getPlayedGames()), 1);
    }

    /**
     * Get KDA
     *
     * @return float
     */
    public function getDeathsAVG()
    {
        return round(($this->deaths / $this->getPlayedGames()), 1);
    }

    /**
     * Get KDA
     *
     * @return float
     */
    public function getAssistsAVG()
    {
        return round(($this->assists / $this->getPlayedGames()), 1);
    }

    /**
     * Get KDA
     *
     * @return float
     */
    public function getCreepsAVG()
    {
        return round(($this->creeps / $this->getPlayedGames()), 1);
    }

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
     * Set regionId
     *
     * @param integer $regionId
     *
     * @return RankedStats
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * Get regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionId;
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
     * Get playedGames
     *
     * @return integer
     */
    public function getPlayedGames()
    {
        return ($this->wins + $this->loses);
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
