<?php

namespace AppBundle\Entity\StaticData\Translation;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rune_translation")
 */
class RuneTranslation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $runeId;

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $languageId;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $description;

    /**
     * Set runeId
     *
     * @param integer $runeId
     *
     * @return RuneTranslation
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

    /**
     * Set languageId
     *
     * @param integer $languageId
     *
     * @return RuneTranslation
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;

        return $this;
    }

    /**
     * Get languageId
     *
     * @return integer
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return RuneTranslation
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
     * Set description
     *
     * @param string $description
     *
     * @return RuneTranslation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
