<?php

namespace AppBundle\Entity\Summoner;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="tier")
 */
class Tier
{

    const UNRANKED = 0;
    const BRONZE = 1;
    const SILVER = 2;
    const GOLD = 3;
    const PLATINUM = 4;
    const DIAMOND = 5;
    const MASTER = 6;
    const CHALLENGER = 7;

    const ID_UNRANKED = 1;
    const ID_BRONZE_5 = 2;
    const ID_BRONZE_4 = 3;
    const ID_BRONZE_3 = 4;
    const ID_BRONZE_2 = 5;
    const ID_BRONZE_1 = 6;
    const ID_SILVER_5 = 7;
    const ID_SILVER_4 = 8;
    const ID_SILVER_3 = 9;
    const ID_SILVER_2 = 10;
    const ID_SILVER_1 = 11;
    const ID_GOLD_5 = 12;
    const ID_GOLD_4 = 13;
    const ID_GOLD_3 = 14;
    const ID_GOLD_2 = 15;
    const ID_GOLD_1 = 16;
    const ID_PLATINUM_5 = 17;
    const ID_PLATINUM_4 = 18;
    const ID_PLATINUM_3 = 19;
    const ID_PLATINUM_2 = 20;
    const ID_PLATINUM_1 = 21;
    const ID_DIAMOND_5 = 22;
    const ID_DIAMOND_4 = 23;
    const ID_DIAMOND_3 = 24;
    const ID_DIAMOND_2 = 25;
    const ID_DIAMOND_1 = 26;
    const ID_MASTER = 27;
    const ID_CHALLENGER = 28;
    
    public function __construct() 
    {
        $this->sumonners = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12)
     * @Assert\Length(max=12)
     * */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $league;

    /**
     * @ORM\Column(type="smallint")
     */
    private $division;

    /**
     * @ORM\OneToMany(targetEntity="Summoner", mappedBy="tier")
     */
    protected $summoners;

    /**
     * Get Image path without the ".png"
     *
     * @return string
     */
    public function getImage()
    {
        if($this->id === self::ID_UNRANKED)
            return 'unranked_';
        else
        {
            $pos = strpos($this->getName(), ' ');
            $rest = substr($this->getName(), 0, $pos);
            switch($this->division)
            {
                default:
                case 1:
                    $division = 'I';
                    break;
                case 2:
                    $division = 'II';
                    break;
                case 3:
                    $division = 'III';
                    break;
                case 4:
                    $division = 'IV';
                    break;
                case 5:
                    $division = 'V';
                    break;
            }
            return strtolower($rest) . '_' . $division;
        }
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
     * @return Tier
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
     * Set league
     *
     * @param integer $league
     *
     * @return Tier
     */
    public function setLeague($league)
    {
        $this->league = $league;

        return $this;
    }

    /**
     * Get league
     *
     * @return integer
     */
    public function getLeague()
    {
        return $this->league;
    }

    /**
     * Set division
     *
     * @param integer $division
     *
     * @return Tier
     */
    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division
     *
     * @return integer
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Add summoner
     *
     * @param \AppBundle\Entity\Summoner\Summoner $summoner
     *
     * @return Tier
     */
    public function addSummoner(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $this->summoners[] = $summoner;

        return $this;
    }

    /**
     * Remove summoner
     *
     * @param \AppBundle\Entity\Summoner\Summoner $summoner
     */
    public function removeSummoner(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $this->summoners->removeElement($summoner);
    }

    /**
     * Get summoners
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSummoners()
    {
        return $this->summoners;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Tier
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
