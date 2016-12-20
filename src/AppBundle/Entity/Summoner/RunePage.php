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
}