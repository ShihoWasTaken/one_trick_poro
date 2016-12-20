<?php

namespace AppBundle\Repository\Summoner;

use Doctrine\ORM\EntityRepository;

class SummonerRepository extends EntityRepository 
{
	public function findAll()
	{
	    return $this->findBy(array(), array('region' => 'DESC', 'name' => 'DESC'));
	}

	public function findSummonersByUserId ($userId) 
	{
		return $this->_em->createQuery("
		select s
		from AppBundle:Summoner\Summoner s
		WHERE s.user_id = :userId
		")
		->setParameter('userId', $userId)
    	->getResult()
		;
	}

	public function findOneByRegionAndSummonerId ($region, $summonerId) 
	{
		return $this->_em->createQuery("
		select s
		from AppBundle:Summoner\Summoner s
		WHERE s.region = :region
		AND s.summonerId = :summonerId
		")
		->setParameter('region', $region)
		->setParameter('summonerId', $summonerId)
    	->getResult()[0]
		;
	}

	public function findOneByRegionAndSummonerIdSafe ($region, $summonerId)
	{
		return $this->_em->createQuery("
		select s
		from AppBundle:Summoner\Summoner s
		WHERE s.region = :region
		AND s.summonerId = :summonerId
		")
			->setParameter('region', $region)
			->setParameter('summonerId', $summonerId)
			->getResult()
			;
	}
/*

	public function getStatusesAndUsers ($deleted) 
	{
		return $this->_em->createQuery("
		select s, u
		from TechCorpFrontBundle:Status s
		join s.user u
		WHERE s.deleted = :deleted
		ORDER BY
		s.createdAt DESC,
		s.id DESC
		")
		->setParameter('deleted', $deleted)
		;
	}

	public function getUserTimeline ($user) {
	return $this->_em->createQuery('
	SELECT s, c, u
	FROM TechCorpFrontBundle:Status s
	LEFT JOIN s.comments c
	LEFT JOIN c.user u
	WHERE
	s.user = :user
	AND s.deleted = false
	ORDER BY
	s.createdAt DESC
	')
	->setParameter('user', $user);
	;
	}

	public function getFriendsTimeline ($user) {
	return $this->_em->createQuery('
	SELECT s, c, u
	FROM TechCorpFrontBundle:Status s
	LEFT JOIN s.comments c
	LEFT JOIN c.user u
	WHERE s.user in (
	SELECT friends FROM
	TechCorpFrontBundle:User uf
	JOIN uf.friends friends
	WHERE uf = :user
	)
	ORDER BY
	s.createdAt DESC
	')
	->setParameter('user', $user);
	;
	}
	*/
}
