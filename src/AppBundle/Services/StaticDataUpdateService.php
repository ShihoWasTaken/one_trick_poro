<?php

namespace AppBundle\Services;

use AppBundle\Entity\StaticData\Champion;
use AppBundle\Entity\StaticData\Rune;
use AppBundle\Entity\StaticData\Mastery;
use AppBundle\Entity\StaticData\Region;
use AppBundle\Services\LoLAPI\LoLAPIService;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class StaticDataUpdateService
{
	private $container;
	private $api;
	private $em;

	public function __construct(Container $container, LoLAPIService $api)
	{
		$this->container = $container;
		$this->api = $api;
		$this->em = $this->container->get('doctrine')->getManager();
	}

	private function endline()
	{
		if (PHP_SAPI === 'cli')
		{
			return PHP_EOL;
		}
		else
		{
			return '<br/>';
		}
	}

	private function img($src)
	{
		if (PHP_SAPI !== 'cli')
		{
			return '<img src="' . $src . '" />"';
		}
	}

	public function updateRegions()
	{
		$updated = false;
		$regions = $this->api->getShards();
		foreach($regions as $region)
		{
			$newRegion = $this->container->get('doctrine')->getRepository('AppBundle:StaticData\Region')->findBy([
				'tag' => $region['region_tag']
			]);
			if(empty($newRegion))
			{
				$newRegion = new Region($region['region_tag'], $region['name'], $region['slug']);
				$this->em->persist($newRegion);
				$updated = true;
				echo 'Région ' . $region['name'] . ' ajoutée' . $this->endline();
			}
		}
		if($updated)
			$this->em->flush();
		else
			echo 'Aucune nouvelle région n\'a été trouvée';
	}

	public function updateChampions()
	{
		$em = $this->container->get('doctrine')->getManager();
		$champions = $this->api->getStaticDataChampions();
		$repository = $this->container->get('doctrine')->getRepository('AppBundle:StaticData\Champion');
		$championsInDatabase = $repository->findAll();
		$updated= false;
		foreach($champions['data'] as $champion)
		{
			$update = true;
			for($i = 0; $i < count($championsInDatabase); $i++)
			{
				if($championsInDatabase[$i]->getId()  == $champion['id'])
					$update = false;
			}
			if($update)
			{
				$updated = true;
				$newChampion = new Champion($champion['id']);
				$newChampion->setKey($champion['key']);
				$em->persist($newChampion);
				echo('Champion ' . $champion['key'] . ' ajoute avec l\'id ' . $champion['id'] . $this->endline());
			}
		}
		if($updated)
			$em->flush();
		else
			echo 'Aucun nouveau champion n\'a été trouvé';
	}

	public function updateRunes()
	{
		$em = $this->container->get('doctrine')->getManager();
		$runes = $this->api->getStaticRunes(null, null, 'all');
		$repository = $this->container->get('doctrine')->getRepository('AppBundle:StaticData\Rune');
		$runesInDatabase = $repository->findAll();
		$updated= false;
		foreach($runes['data'] as $rune)
		{
			$update = true;
			for($i = 0; $i < count($runesInDatabase); $i++)
			{
				if($runesInDatabase[$i]->getId()  == $rune['id'])
					$update = false;
			}
			if($update)
			{
				$updated = true;
				$newRune = new Rune($rune['id']);
				$newRune->setImage($rune['image']['full']);
				$newRune->setType($rune['rune']['type']);
				$newRune->setTier($rune['rune']['tier']);
				$em->persist($newRune);
				echo('Rune ' . $this->img('http://ddragon.leagueoflegends.com/cdn/6.24.1/img/rune/' . $rune['image']['full']) . ' ajoutée avec l\'id ' . $rune['id'] . $this->endline());
			}
		}
		if($updated)
			$em->flush();
		else
			echo 'Aucune nouvelle rune n\'a été trouvée';
	}

	public function updateMasteries()
	{
		$em = $this->container->get('doctrine')->getManager();
		$masteries = $this->api->getStaticMasteries(null, null, 'all');
		$repository = $this->container->get('doctrine')->getRepository('AppBundle:StaticData\Mastery');
		$masteriesInDatabase = $repository->findAll();
		$updated= false;
		foreach($masteries['data'] as $mastery)
		{
			$update = true;
			for($i = 0; $i < count($masteriesInDatabase); $i++)
			{
				if($masteriesInDatabase[$i]->getId()  == $mastery['id'])
					$update = false;
			}
			if($update)
			{
				$updated = true;
				$newMastery = new Mastery($mastery['id']);
				$newMastery->setMaxRank($mastery['ranks']);
				$newMastery->setMasteryTree($mastery['masteryTree']);
				$newMastery->setImage($mastery['image']['full']);
				$em->persist($newMastery);
				echo('Mastery ' . $this->img('http://ddragon.leagueoflegends.com/cdn/6.24.1/img/mastery/' . $mastery['image']['full']) . ' ajoutée avec l\'id ' . $mastery['id'] . $this->endline());
			}
		}
		if($updated)
			$em->flush();
		else
			echo 'Aucune nouvelle maitrise n\'a été trouvée';
	}
}
