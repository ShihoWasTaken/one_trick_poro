<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Summoner\Tier;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTierData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $leagues = array('Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond');
    private $nonStandardsLeague = array('Unranked', 'Master', 'Challenger');

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
        foreach($this->nonStandardsLeague as $league)
        {
            $tier = new Tier();
            switch($league)
            {
                default:
                case 'Unranked':
                    $leagueId = Tier::UNRANKED;
                    $id = Tier::ID_UNRANKED;
                    break;
                case 'Master':
                    $leagueId = Tier::MASTER;
                    $id = Tier::ID_MASTER;
                    break;
                case 'Challenger':
                    $leagueId = Tier::CHALLENGER;
                    $id = Tier::ID_CHALLENGER;
                    break;
            }
            $tier->setId($id);
            $tier->setLeague($leagueId);
            $tier->setDivision(1);
            $tier->setName($league);
            $manager->persist($tier);
        }
        foreach($this->leagues as $league)
        {
            for($i=0;$i<5;$i++)
            {
                $tier = new Tier();
                switch($league)
                {
                    default:
                    case 'Bronze':
                    $leagueId = Tier::BRONZE;
                        $id = Tier::ID_BRONZE_5;
                        break;
                    case 'Silver':
                        $leagueId = Tier::SILVER;
                        $id = Tier::ID_SILVER_5;
                        break;
                    case 'Gold':
                        $leagueId = Tier::GOLD;
                        $id = Tier::ID_GOLD_5;
                        break;
                    case 'Platinum':
                        $leagueId = Tier::PLATINUM;
                        $id = Tier::ID_PLATINUM_5;
                        break;
                    case 'Diamond':
                        $leagueId = Tier::DIAMOND;
                        $id = Tier::ID_DIAMOND_5;
                        break;
                }
                $tier->setId($id + $i);
                $tier->setLeague($leagueId);
                $tier->setDivision($i+1);
                $tier->setName($league . ' ' . strval($i+1));
                $manager->persist($tier);
            }
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}