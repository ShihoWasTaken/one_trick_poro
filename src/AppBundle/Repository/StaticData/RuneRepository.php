<?php

namespace AppBundle\Repository\StaticData;

use Doctrine\ORM\EntityRepository;

class RuneRepository extends EntityRepository
{
    public function findAllIn(array $ids)
    {
        return $this->_em->createQuery("
		select r
		from AppBundle:StaticData\Rune r
		WHERE r.id IN (:ids)
		")
            ->setParameter('ids', $ids)
            ->getResult();
    }
}
