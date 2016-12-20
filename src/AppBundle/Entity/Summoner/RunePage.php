<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rune_page")
 */
class RunePage
{
    public function __construct($summonerId, $pageId, $slotId)
    {
        $this->summonerId = $summonerId;
        $this->pageId = $pageId;
        $this->slotId = $slotId;
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
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pageId;

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $slotId;

    /**
     * @ORM\Column(name="rune_id", type="smallint")
     * @ORM\OneToOne(targetEntity="Rune")
     */
    private $runeId;

    /**
     * Set summonerId
     *
     * @param integer $summonerId
     *
     * @return RunePage
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
     * @return RunePage
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
     * Set slotId
     *
     * @param integer $slotId
     *
     * @return RunePage
     */
    public function setSlotId($slotId)
    {
        $this->slotId = $slotId;

        return $this;
    }

    /**
     * Get slotId
     *
     * @return integer
     */
    public function getSlotId()
    {
        return $this->slotId;
    }

    /**
     * Set runeId
     *
     * @param integer $runeId
     *
     * @return RunePage
     */
    public function setRuneId($runeId)
    {
        $this->runeId = $runeId;

        return $this;
    }

    /**
     * Get runeId
     *
     * @return integer
     */
    public function getRuneId()
    {
        return $this->runeId;
    }
}