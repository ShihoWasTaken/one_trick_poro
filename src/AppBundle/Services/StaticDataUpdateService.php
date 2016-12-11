<?php

namespace AppBundle\Services;

use AppBundle\Entity\StaticData\Champion;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Services\LoLAPI\LoLAPIService;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\Query;

class StaticDataUpdateService
{
	private $container;
	private $api;

	public function __construct(Container $container, LoLAPIService $api)
	{
		$this->container = $container;
		$this->api = $api;
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
				//echo $championsInDatabase[$i]->getId() . ' - '. $championsInDatabase[$i]->getKey() . '<br>';
				if($championsInDatabase[$i]->getId()  == $champion['id'])
					$update = false;
			}
			if($update)
			{
				$updated = true;
				$newChampion = new Champion($champion['id']);
				$newChampion->setKey($champion['key']);
				$em->persist($newChampion);
				echo('Champion ' . $champion['key'] . ' ajoute avec l\'id ' . $champion['id'] . '<br>');
			}
		}
		if($updated)
			$em->flush();
		else
			echo 'Aucun nouveau champion n\'a été trouvé';
	}
}
