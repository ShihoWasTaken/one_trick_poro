<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\StaticData\Region;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRegionData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $region_br = new Region('Brazil', 'br', 'br1.api.riotgames.com');
        $manager->persist($region_br);

        $region_eune = new Region('EU Nordic & East', 'eune', 'eun1.api.riotgames.com');
        $manager->persist($region_eune);

        $region_euw = new Region('EU West', 'euw', 'euw1.api.riotgames.com');
        $manager->persist($region_euw);

        $region_jp = new Region('Japan', 'jp', 'jp1.api.riotgames.com');
        $manager->persist($region_jp);

        $region_kr = new Region('Republic of Korea', 'kr', 'kr.api.riotgames.com');
        $manager->persist($region_kr);

        $region_lan = new Region('Latin America North', 'lan', 'la1.api.riotgames.com');
        $manager->persist($region_lan);

        $region_las = new Region('Latin America South', 'las', 'la2.api.riotgames.com');
        $manager->persist($region_las);

        $region_na = new Region('North America', 'na', 'na1.api.riotgames.com');
        $manager->persist($region_na);

        $region_oce = new Region('Oceania', 'oce', 'oc1.api.riotgames.com');
        $manager->persist($region_oce);

        $region_tr = new Region('Turkey', 'tr', 'tr1.api.riotgames.com');
        $manager->persist($region_tr);

        $region_ru = new Region('Russia', 'ru', 'ru.api.riotgames.com');
        $manager->persist($region_ru);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}