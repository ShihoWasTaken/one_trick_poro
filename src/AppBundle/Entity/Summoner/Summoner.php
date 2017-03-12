<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use  AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Summoner\SummonerRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="summoner")
 */
class Summoner
{
    //TODO: Rajouter league points
    const UPDATE_INTERVAL = 60 * 30; // 30 min

    public function __construct($summonerId, \AppBundle\Entity\StaticData\Region $region)
    {
        $this->id = $summonerId;
        $this->region = $region;
        $this->rankedStats = new ArrayCollection();
        $this->firstUpdated = false;

    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\StaticData\Region")
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="summoners")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(name="Name", type="string", length=16)
     * @Assert\Length(max=16)
     * */
    private $name;

    /**
     * @ORM\Column(name="ProfileIconId", type="smallint")
     * */
    private $profileIconId;


    /**
     * @ORM\Column(name="RevisionDate", type="datetime")
     * */
    private $revisionDate;


    /**
     * @ORM\Column(name="Level", type="smallint")
     * */
    private $level;

    /**
     * @ORM\ManyToOne(targetEntity="Tier", inversedBy="summoners")
     * @ORM\JoinColumn(name="tier_id", referencedColumnName="id")
     */
    private $tier;

    /**
     * @ORM\Column(type="boolean")
     * */
    private $firstUpdated;

    // TODO: les infos suivantes doivent être affichés seulement si Tier n'est pas null

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
     * @ORM\Column(name="LastUpdateDate", type="datetime")
     * */
    private $lastUpdateDate;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateLastUpdateDate()
    {
        $date = date_create();
        date_timestamp_set($date, time());
        $this->lastUpdateDate = $date;
    }

    public function isUpdatable()
    {
        $last_update = $this->lastUpdateDate + self::UPDATE_INTERVAL;
        $now = date_create();
        date_timestamp_set($now, time());
        return ($last_update < $now);
    }


    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Summoner
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
     * Set name
     *
     * @param string $name
     *
     * @return Summoner
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set profileIconId
     *
     * @param integer $profileIconId
     *
     * @return Summoner
     */
    public function setProfileIconId($profileIconId)
    {
        $this->profileIconId = $profileIconId;

        return $this;
    }

    /**
     * Get profileIconId
     *
     * @return integer
     */
    public function getProfileIconId()
    {
        return $this->profileIconId;
    }
    
    /**
     * Set revisionDate
     *
     * @param \DateTime $revisionDate
     *
     * @return Summoner
     */
    public function setRevisionDate($revisionDate)
    {
        $this->revisionDate = $revisionDate;

        return $this;
    }

    /**
     * Get revisionDate
     *
     * @return \DateTime
     */
    public function getRevisionDate()
    {
        return $this->revisionDate;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return Summoner
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
     * Set lastUpdateDate
     *
     * @param \DateTime $lastUpdateDate
     *
     * @return Summoner
     */
    public function setLastUpdateDate($lastUpdateDate)
    {
        $this->lastUpdateDate = $lastUpdateDate;

        return $this;
    }

    /**
     * Get lastUpdateDate
     *
     * @return \DateTime
     */
    public function getLastUpdateDate()
    {
        return $this->lastUpdateDate;
    }

    /**
     * Set region
     *
     * @param \AppBundle\Entity\StaticData\Region $region
     *
     * @return Summoner
     */
    public function setRegion(\AppBundle\Entity\StaticData\Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \AppBundle\Entity\StaticData\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Summoner
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set tier
     *
     * @param \AppBundle\Entity\Summoner\Tier $tier
     *
     * @return Summoner
     */
    public function setTier(\AppBundle\Entity\Summoner\Tier $tier = null)
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
     * Set firstUpdated
     *
     * @param boolean $firstUpdated
     *
     * @return Summoner
     */
    public function setFirstUpdated($firstUpdated)
    {
        $this->firstUpdated = $firstUpdated;

        return $this;
    }

    /**
     * Get firstUpdated
     *
     * @return boolean
     */
    public function isFirstUpdated()
    {
        return $this->firstUpdated;
    }

    /**
     * Get firstUpdated
     *
     * @return boolean
     */
    public function getFirstUpdated()
    {
        return $this->firstUpdated;
    }

    /**
     * Set leaguePoints
     *
     * @param integer $leaguePoints
     *
     * @return Summoner
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
     * @return Summoner
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
     * @return Summoner
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
     * @return Summoner
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
     * @return Summoner
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
     * @return Summoner
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
     * @return Summoner
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

    /**
     * Set miniSeries
     *
     * @param string $miniSeries
     *
     * @return Summoner
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
}
