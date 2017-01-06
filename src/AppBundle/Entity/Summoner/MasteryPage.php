<?php

namespace AppBundle\Entity\Summoner;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mastery_page")
 */
class MasteryPage
{
    public function __construct($summonerId, $regionId, $pageId, $masteryId)
    {
        $this->summonerId = $summonerId;
        $this->regionId = $regionId;
        $this->pageId = $pageId;
        $this->masteryId = $masteryId;
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
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pageId;

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\OneToOne(targetEntity="Mastery")
     */
    private $masteryId;


    /**
     * Set summonerId
     *
     * @param integer $summonerId
     *
     * @return MasteryPage
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
     * Set pageId
     *
     * @param integer $pageId
     *
     * @return MasteryPage
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * Get pageId
     *
     * @return integer
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set masteryId
     *
     * @param integer $masteryId
     *
     * @return MasteryPage
     */
    public function setMasteryId($masteryId)
    {
        $this->masteryId = $masteryId;

        return $this;
    }

    /**
     * Get masteryId
     *
     * @return integer
     */
    public function getMasteryId()
    {
        return $this->masteryId;
    }

    /**
     * Set regionId
     *
     * @param integer $regionId
     *
     * @return MasteryPage
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
}
