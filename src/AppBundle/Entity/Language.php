<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="language")
 */
class Language
{

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $symfonyLocale;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $localeCode;


    /**
     * @ORM\Column(type="string", length=16)
     */
    private $name;

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Language
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
     * Set symfonyLocale
     *
     * @param string $symfonyLocale
     *
     * @return Language
     */
    public function setSymfonyLocale($symfonyLocale)
    {
        $this->symfonyLocale = $symfonyLocale;

        return $this;
    }

    /**
     * Get symfonyLocale
     *
     * @return string
     */
    public function getSymfonyLocale()
    {
        return $this->symfonyLocale;
    }

    /**
     * Set localeCode
     *
     * @param string $localeCode
     *
     * @return Language
     */
    public function setLocaleCode($localeCode)
    {
        $this->localeCode = $localeCode;

        return $this;
    }

    /**
     * Get localeCode
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Language
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
}
