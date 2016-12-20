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

    public function __construct() 
    {
        $this->sumonners = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="Name", type="string", length=12)
     * @Assert\Length(max=12)
     * */
    private $name;

    /**
     * @ORM\Column(name="League", type="smallint")
     */
    private $league;

    /**
     * @ORM\Column(name="Division", type="smallint")
     */
    private $division;

    /**
     * @ORM\OneToMany(targetEntity="Summoner", mappedBy="tier")
     */
    protected $summoners;


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
}
