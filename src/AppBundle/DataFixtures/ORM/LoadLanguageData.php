<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


use AppBundle\Entity\Language;

class LoadLanguageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $locales = array('en', 'fr');
        $localeCodes = array('en_US', 'fr_FR');
        $names = array('English', 'Français');
        for ($i=0; $i < count($locales); ++$i)
        {
            $language = new Language();
            $language->setId($i + 1);
            $language->setName($names[$i]);
            $language->setSymfonyLocale($locales[$i]);
            $language->setLocaleCode($localeCodes[$i]);
            $manager->persist($language);
            $this->addReference('language'.$i, $language);

        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}