<?php

namespace AppBundle\Entity\StaticData;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="region",uniqueConstraints={
 * @ORM\UniqueConstraint(name="uniqueRegionTag", columns={"host"}),
 * @ORM\UniqueConstraint(name="uniqueRegionName", columns={"name"}),
 * @ORM\UniqueConstraint(name="uniqueRegionSlug", columns={"slug"})
 * })
 */
class Region
{
    public function __construct($name, $slug, $host)
    {
        $this->host = $host;
        $this->name = $name;
        $this->slug = $slug;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=24)
     */
    private $host;

    /**
     * @ORM\Column(type="string", length=24)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $slug;

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
     * Set host
     *
     * @param string $host
     *
     * @return Region
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Region
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Region
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
