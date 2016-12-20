<?php


namespace AppBundle\Entity\StaticData;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="item")
 */
class Item
{
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;
}