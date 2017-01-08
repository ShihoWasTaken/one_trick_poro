<?php

namespace AppBundle\Entity\StaticData\Translation;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mastery_translation")
 */
class MasteryTranslation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $masteryId;

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $languageId;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2048)
     */
    private $description;

    /**
     * Get description
     *
     * @return string
     */
    public function getFormattedDescription()
    {
        return str_replace('|', '<br>', $this->description);
    }

    /**
     * Set masteryId
     *
     * @param integer $masteryId
     *
     * @return MasteryTranslation
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
     * Set languageId
     *
     * @param integer $languageId
     *
     * @return MasteryTranslation
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
     * @return MasteryTranslation
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
     * @return MasteryTranslation
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
