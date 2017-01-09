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
    const UPDATE_INTERVAL = 60*60; // 1h

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
}
