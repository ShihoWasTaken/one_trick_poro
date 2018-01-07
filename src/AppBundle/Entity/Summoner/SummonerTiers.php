<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="summoner_tier", uniqueConstraints={@ORM\UniqueConstraint(name="UK_queue_per_summonerId_per_region", columns={"summoner_id", "region_id", "queue_id"})})
 */
class SummonerTiers
{

    const SOLO_DUO = 1;
    const FLEX_5v5 = 2;
    const FLEX_3v3 = 3;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Summoner", inversedBy="tiers")
     */
    protected $summoner;

    // TODO: rajouter many to one pour cohérence BDD
    /**
     * @ORM\Column(type="smallint")
     */
    protected $regionId;

    // TODO: rajouter many to one pour cohérence BDD
    /**
     * @ORM\Column(type="smallint")
     */
    private $queueId;

    /**
     * @ORM\ManyToOne(targetEntity="Tier")
     * @ORM\JoinColumn(name="tier_id", referencedColumnName="id")
     * */
    protected $tier;

    /**
     * @ORM\Column(type="smallint")
     *
     */
    private $leaguePoints = 0;


    /**
     * @ORM\Column(type="integer")
     *
     */
    private $wins = 0;

    /**
     * @ORM\Column(type="integer")
     *
     */
    private $losses = 0;

    /**
     * @ORM\Column(type="boolean")
     *
     */
    private $freshBlood = false;

    /**
     * @ORM\Column(type="boolean")
     *
     */
    private $hotStreak = false;

    /**
     * @ORM\Column(type="boolean")
     *
     */
    private $inactive = false;

    /**
     * @ORM\Column(type="boolean")
     *
     */
    private $veteran = false;

    /**
     * @ORM\Column(type="string", length=5)
     *
     */
    private $miniSeries = "";

    /**
     * @param $tier1
     * @param $tier2
     */
    public static function getHigherRank(SummonerTiers $tier1, SummonerTiers $tier2)
    {
        // Si le rang est différent entre les deux
        if ($tier1->getTier()->getId() != $tier2->getTier()->getId()) {
            return $tier1->getTier()->getId() > $tier2->getTier()->getId() ? $tier1 : $tier2;
        } // Sinon même palier
        else {
            // Si les points sont différents entre les deux
            if ($tier1->getLeaguePoints() != $tier2->getLeaguePoints()) {
                return $tier1->getLeaguePoints() > $tier2->getLeaguePoints() ? $tier1 : $tier2;
            } // Sinon on renvoie le 1er
            else {
                return $tier1;
            }
        }
    }

    /**
     * Set queueId
     *
     * @param integer $queueId
     *
     * @return SummonerTiers
     */
    public function setQueueId($queueId)
    {
        $this->queueId = $queueId;

        return $this;
    }

    /**
     * Get queueId
     *
     * @return integer
     */
    public function getQueueId()
    {
        return $this->queueId;
    }

    /**
     * Set leaguePoints
     *
     * @param integer $leaguePoints
     *
     * @return SummonerTiers
     */
    public function setLeaguePoints($leaguePoints)
    {
        $this->leaguePoints = $leaguePoints;

        return $this;
    }

    /**
     * Get leaguePoints
     *
     * @return integer
     */
    public function getLeaguePoints()
    {
        return $this->leaguePoints;
    }

    /**
     * Set wins
     *
     * @param integer $wins
     *
     * @return SummonerTiers
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
     * Set losses
     *
     * @param integer $losses
     *
     * @return SummonerTiers
     */
    public function setLosses($losses)
    {
        $this->losses = $losses;

        return $this;
    }

    /**
     * Get losses
     *
     * @return integer
     */
    public function getLosses()
    {
        return $this->losses;
    }

    /**
     * Set freshBlood
     *
     * @param boolean $freshBlood
     *
     * @return SummonerTiers
     */
    public function setFreshBlood($freshBlood)
    {
        $this->freshBlood = $freshBlood;

        return $this;
    }

    /**
     * Get freshBlood
     *
     * @return boolean
     */
    public function isFreshBlood()
    {
        return $this->freshBlood;
    }

    /**
     * Set hotStreak
     *
     * @param boolean $hotStreak
     *
     * @return SummonerTiers
     */
    public function setHotStreak($hotStreak)
    {
        $this->hotStreak = $hotStreak;

        return $this;
    }

    /**
     * Get hotStreak
     *
     * @return boolean
     */
    public function isHotStreak()
    {
        return $this->hotStreak;
    }

    /**
     * Set inactive
     *
     * @param boolean $inactive
     *
     * @return SummonerTiers
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;

        return $this;
    }

    /**
     * Get inactive
     *
     * @return boolean
     */
    public function isInactive()
    {
        return $this->inactive;
    }

    /**
     * Set veteran
     *
     * @param boolean $veteran
     *
     * @return SummonerTiers
     */
    public function setVeteran($veteran)
    {
        $this->veteran = $veteran;

        return $this;
    }

    /**
     * Get veteran
     *
     * @return boolean
     */
    public function isVeteran()
    {
        return $this->veteran;
    }

    /**
     * Set miniSeries
     *
     * @param string $miniSeries
     *
     * @return SummonerTiers
     */
    public function setMiniSeries($miniSeries)
    {
        $this->miniSeries = $miniSeries;

        return $this;
    }

    /**
     * Get miniSeries
     *
     * @return string
     */
    public function getMiniSeries()
    {
        return $this->miniSeries;
    }

    /**
     * Set tier
     *
     * @param \AppBundle\Entity\Summoner\Tier $tier
     *
     * @return SummonerTiers
     */
    public function setTier(\AppBundle\Entity\Summoner\Tier $tier)
    {
        $this->tier = $tier;

        return $this;
    }

    /**
     * Get tier
     *
     * @return \AppBundle\Entity\Summoner\Tier
     */
    public function getTier()
    {
        return $this->tier;
    }

    /**
     * Set summoner
     *
     * @param \AppBundle\Entity\Summoner\Summoner $summoner
     *
     * @return SummonerTiers
     */
    public function setSummoner(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $this->summoner = $summoner;

        return $this;
    }

    /**
     * Get summoner
     *
     * @return \AppBundle\Entity\Summoner\Summoner
     */
    public function getSummoner()
    {
        return $this->summoner;
    }

    /**
     * Set regionId
     *
     * @param integer $regionId
     *
     * @return SummonerTiers
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get freshBlood
     *
     * @return boolean
     */
    public function getFreshBlood()
    {
        return $this->freshBlood;
    }

    /**
     * Get hotStreak
     *
     * @return boolean
     */
    public function getHotStreak()
    {
        return $this->hotStreak;
    }

    /**
     * Get inactive
     *
     * @return boolean
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * Get veteran
     *
     * @return boolean
     */
    public function getVeteran()
    {
        return $this->veteran;
    }
}
