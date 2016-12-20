<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mastery_page")
 */
class MasteryPage
{
    public function __construct($summonerId, $pageId, $masteryId)
    {
        $this->summonerId = $summonerId;
        $this->pageId = $pageId;
        $this->masteryId = $masteryId;
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
     * @ORM\OneToOne(targetEntity="Mastery")
     */
    private $masteryId;

}