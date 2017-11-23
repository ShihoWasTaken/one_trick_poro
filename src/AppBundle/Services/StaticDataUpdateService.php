<?php

namespace AppBundle\Services;

use AppBundle\Entity\StaticData\Champion;
use AppBundle\Entity\StaticData\Rune;
use AppBundle\Entity\StaticData\Mastery;
use AppBundle\Entity\StaticData\Region;
use AppBundle\Entity\StaticData\Item;
use AppBundle\Entity\StaticData\Translation\MasteryTranslation;
use AppBundle\Entity\StaticData\Translation\RuneTranslation;
use AppBundle\Entity\StaticData\Translation\ChampionTranslation;
use AppBundle\Services\LoLAPI\LoLAPIService;
use Doctrine\Bundle\DoctrineBundle\Registry;

class StaticDataUpdateService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    /**
     * @var \AppBundle\Services\LoLAPI\LoLAPIService
     */
    private $api;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(Registry $doctrine, LoLAPIService $api)
    {
        $this->doctrine = $doctrine;
        $this->api = $api;
        $this->em = $doctrine->getManager();
    }

    private function endline()
    {
        if (PHP_SAPI === 'cli') {
            return PHP_EOL;
        } else {
            return '<br/>';
        }
    }

    private function img($src)
    {
        if (PHP_SAPI !== 'cli') {
            return '<img src="' . $src . '" />"';
        }
    }

    public function updateChampions()
    {
        $em = $this->doctrine->getManager();
        $region = $this->doctrine->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => 'euw'
        ]);
        $champions = $this->api->getStaticDataChampions($region);
        $repository = $this->doctrine->getRepository('AppBundle:StaticData\Champion');
        $championsInDatabase = $repository->findAll();
        $updated = false;
        $championsUpdated = array();
        foreach ($champions['data'] as $champion) {
            $update = true;
            for ($i = 0; $i < count($championsInDatabase); $i++) {
                if ($championsInDatabase[$i]->getId() == $champion['id'])
                    $update = false;
            }
            if ($update) {
                $updated = true;
                $newChampion = new Champion($champion['id']);
                $newChampion->setKey($champion['key']);
                $em->persist($newChampion);
                $championsUpdated[] = $champion['id'];
                echo('Champion ' . $champion['key'] . ' ajoute avec l\'id ' . $champion['id'] . $this->endline());
            }
        }
        if ($updated)
            $em->flush();
        else
            echo 'Aucun nouveau champion n\'a été trouvé' . $this->endline();
        $languages = $this->doctrine->getRepository('AppBundle:Language')->findAll();
        foreach ($languages as $language) {
            $championTranslationData = $this->api->getStaticDataChampions($region, $language->getLocaleCode(), null, null, null);
            foreach ($championTranslationData['data'] as $data) {
                if (in_array($data['id'], $championsUpdated)) {
                    $championTranslation = new ChampionTranslation();
                    $championTranslation->setChampionId($data['id']);
                    $championTranslation->setLanguageId($language->getId());
                    $championTranslation->setName($data['name']);
                    $championTranslation->setTitle($data['title']);
                    $em->persist($championTranslation);
                }
            }
            if ($updated)
                echo('Traduction ' . $language->getSymfonyLocale() . ' des champions ajoutées ' . $this->endline());
        }
        $em->flush();
    }

    public function updateRunes()
    {
        $em = $this->doctrine->getManager();
        $region = $this->doctrine->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => 'euw'
        ]);
        $runes = $this->api->getStaticRunes($region, null, null, 'all');
        $repository = $this->doctrine->getRepository('AppBundle:StaticData\Rune');
        $runesInDatabase = $repository->findAll();
        $updated = false;
        $runesUpdated = array();
        foreach ($runes['data'] as $rune) {
            $update = true;
            for ($i = 0; $i < count($runesInDatabase); $i++) {
                if ($runesInDatabase[$i]->getId() == $rune['id'])
                    $update = false;
            }
            if ($update) {
                $updated = true;
                $newRune = new Rune($rune['id']);
                $newRune->setImage($rune['image']['full']);
                $newRune->setType($rune['rune']['type']);
                $newRune->setTier($rune['rune']['tier']);
                $em->persist($newRune);
                $runesUpdated[] = $rune['id'];
                echo('Rune ' . $this->img('http://ddragon.leagueoflegends.com/cdn/6.24.1/img/rune/' . $rune['image']['full']) . ' ajoutée avec l\'id ' . $rune['id'] . $this->endline());
            }
        }
        if ($updated)
            $em->flush();
        else
            echo 'Aucune nouvelle rune n\'a été trouvée' . $this->endline();
        $languages = $this->doctrine->getRepository('AppBundle:Language')->findAll();
        foreach ($languages as $language) {
            $runesTranslationData = $this->api->getStaticRunes($region, $language->getLocaleCode(), null, null);
            foreach ($runesTranslationData['data'] as $data) {
                if (in_array($data['id'], $runesUpdated)) {
                    $runeTranslation = new RuneTranslation();
                    $runeTranslation->setRuneId($data['id']);
                    $runeTranslation->setLanguageId($language->getId());
                    $runeTranslation->setName($data['name']);
                    $runeTranslation->setDescription($data['description']);
                    $em->persist($runeTranslation);
                }
            }
            if ($updated)
                echo('Traduction ' . $language->getSymfonyLocale() . ' des runes ajoutées ' . $this->endline());
        }
        $em->flush();
    }

    public function updateMasteries()
    {
        $em = $this->doctrine->getManager();
        $languages = $this->doctrine->getRepository('AppBundle:Language')->findAll();
        $region = $this->doctrine->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => 'euw'
        ]);
        $masteries = $this->api->getStaticMasteries($region, null, null, 'all');
        $repository = $this->doctrine->getRepository('AppBundle:StaticData\Mastery');
        $masteriesInDatabase = $repository->findAll();
        $updated = false;
        $masteriesUpdated = array();
        foreach ($masteries['data'] as $mastery) {
            $update = true;
            for ($i = 0; $i < count($masteriesInDatabase); $i++) {
                if ($masteriesInDatabase[$i]->getId() == $mastery['id'])
                    $update = false;
            }
            if ($update) {
                $updated = true;
                $newMastery = new Mastery($mastery['id']);
                $newMastery->setMaxRank($mastery['ranks']);
                $newMastery->setMasteryTree($mastery['masteryTree']);
                $newMastery->setImage($mastery['image']['full']);
                $em->persist($newMastery);
                $masteriesUpdated[] = $mastery['id'];
                echo('Mastery ' . $this->img('http://ddragon.leagueoflegends.com/cdn/6.24.1/img/mastery/' . $mastery['image']['full']) . ' ajoutée avec l\'id ' . $mastery['id'] . $this->endline());
            }
        }
        if ($updated)
            $em->flush();
        else
            echo 'Aucune nouvelle maitrise n\'a été trouvée' . $this->endline();
        foreach ($languages as $language) {
            $masteryTranslationData = $this->api->getStaticMasteries($region, $language->getLocaleCode(), null, null);
            foreach ($masteryTranslationData['data'] as $data) {
                if (in_array($data['id'], $masteriesUpdated)) {
                    $MasteryTranslation = new MasteryTranslation();
                    $MasteryTranslation->setMasteryId($data['id']);
                    $MasteryTranslation->setLanguageId($language->getId());
                    $MasteryTranslation->setName($data['name']);
                    $MasteryTranslation->setDescription(implode('|', $data['description']));
                    $em->persist($MasteryTranslation);
                }
            }
            if ($updated)
                echo('Traduction ' . $language->getSymfonyLocale() . ' des masteries ajoutées ' . $this->endline());
        }
        $em->flush();
    }

    public function updateItems()
    {
        $em = $this->doctrine->getManager();
        $languages = $this->doctrine->getRepository('AppBundle:Language')->findAll();
        $region = $this->doctrine->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => 'euw'
        ]);
        $items = $this->api->getStaticDataItems($region, null, null, 'all');
        $repository = $this->doctrine->getRepository('AppBundle:StaticData\Item');
        $itemsInDatabase = $repository->findAll();
        $updated = false;
        foreach ($items['data'] as $item) {
            $update = true;
            for ($i = 0; $i < count($itemsInDatabase); $i++) {
                if ($itemsInDatabase[$i]->getId() == $item['id'])
                    $update = false;
            }
            if ($update) {
                $updated = true;
                $newItem = new Item($item['id']);
                $em->persist($newItem);
                echo('Item ' . $this->img('http://ddragon.leagueoflegends.com/cdn/6.24.1/img/item/' . $item['id']) . ' ajoutée avec l\'id ' . $item['id'] . $this->endline());
            }
        }
        if ($updated)
            $em->flush();
        else
            echo 'Aucun nouvel item n\'a été trouvé' . $this->endline();
        // TODO: faire la traductions des items
        /*
        foreach($languages as $language)
        {
            $masteryTranslationData = $this->api->getStaticMasteries($region, $language->getLocaleCode(), null, null);
            foreach($masteryTranslationData['data'] as $data)
            {
                $MasteryTranslation = new MasteryTranslation();
                $MasteryTranslation->setMasteryId($data['id']);
                $MasteryTranslation->setLanguageId($language->getId());
                $MasteryTranslation->setName($data['name']);
                $MasteryTranslation->setDescription(implode('|', $data['description']));
                $em->persist($MasteryTranslation);
            }
            echo('Traduction ' . $language->getSymfonyLocale() . ' des masteries ajoutées ' . $this->endline());
        }
        $em->flush();
        */
    }
}
