<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Tier;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTierData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $leagues = array('Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond');

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
        $faker = \Faker\Factory::create();



        foreach($this->leagues as $league)
        {
            for($i=0;$i<5;$i++)
            {
                $tier = new Tier();
                $tier->setLeague($league);
                $tier->setDivision($i+1);
                $tier->setName($league . ' ' . $i+1);
                $manager->persist($tier);
                //$this->addReference('tier'.$i, $tier);
            }
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}