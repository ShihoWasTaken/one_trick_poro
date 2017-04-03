<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Summoner\SummonerRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="summoner",uniqueConstraints={@ORM\UniqueConstraint(name="UK_summonerId_per_region", columns={"summoner_id", "region_id"})})
 */
class Summoner
{
    const UPDATE_INTERVAL = 60 * 30; // 30 min

    public function __construct($summonerId, \AppBundle\Entity\StaticData\Region $region)
    {
        $this->summonerId = $summonerId;
        $this->region = $region;
        $this->tiers = new ArrayCollection();
        $this->rankedStats = new ArrayCollection();
        $this->firstUpdated = false;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\Column(name="summoner_id", type="integer")
     */
    private $summonerId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\StaticData\Region")
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
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
     * @ORM\OneToMany(targetEntity="SummonerTiers", mappedBy="summoner", cascade={"all"})
     */
    protected $tiers;

    /**
     * @ORM\Column(type="boolean")
     * */
    private $firstUpdated;


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

    public function secondsBeforeNextUpdate()
    {
        $date = $this->lastUpdateDate->getTimestamp() + self::UPDATE_INTERVAL;
        $now = date_create();
        date_timestamp_set($now, time());
        return ($date - $now->getTimestamp());
    }

    public function isUpdatable()
    {
        $date = $this->lastUpdateDate->getTimestamp() + self::UPDATE_INTERVAL;
        $now = date_create();
        date_timestamp_set($now, time());
        return ($date < $now->getTimestamp());
    }

    public function isRefreshed()
    {
        $elapsed = time() - strtotime($this->lastUpdateDate);
        if ($elapsed > self::UPDATE_INTERVAL) {
            return false;
        } else {
            return true;
        }
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
     * Set summoner id
     *
     * @param integer $summonerId
     *
     * @return Summoner
     */
    public function setSummonerId($summonerId)
    {
        $this->summonerId = $summonerId;

        return $this;
    }

    /**
     * Get summoner id
     *
     * @return integer
     */
    public function getSummonerId()
    {
        return $this->summonerId;
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
    public function getFirstUpdated()
    {
        return $this->firstUpdated;
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
     * Add tier
     *
     * @param \AppBundle\Entity\Summoner\SummonerTiers $tier
     *
     * @return Summoner
     */
    public function addTier(\AppBundle\Entity\Summoner\SummonerTiers $tier)
    {
        $this->tiers[] = $tier;

        return $this;
    }

    /**
     * Remove tier
     *
     * @param \AppBundle\Entity\Summoner\SummonerTiers $tier
     */
    public function removeTier(\AppBundle\Entity\Summoner\SummonerTiers $tier)
    {
        $this->tiers->removeElement($tier);
    }

    /**
     * Get tiers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTiers()
    {
        return $this->tiers;
    }
}
