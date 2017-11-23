<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Country;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Finder\Finder;

class LoadCountryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $kernel = $this->container->get('kernel');
        $em = $this->container->get('doctrine')->getManager();
        try {
            $path = $kernel->locateResource('@AppBundle/Resources/public/fixtures/countries.json');
            $json = file_get_contents($path);
            $decoded = json_decode($json, true);
            // Stocker sur 48 octets
            foreach($decoded as $code => $name)
            {
                $country = new \AppBundle\Entity\UserStatistic\Country($name, $code);
                $em->persist($country);
            }
            $em->flush();
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("Le fichier JSON contenant la liste des pays est introuvable", 500);
        }
    }

    public function getOrder()
    {
        return 1;
    }
}
