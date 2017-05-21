<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\Summoner\Summoner;
use AppBundle\Entity\Summoner\RankedStats;
use AppBundle\Entity\Summoner\SummonerTiers;
use AppBundle\Entity\Summoner\ChampionMastery;
use AppBundle\Entity\Summoner\Tier;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Services\LoLAPI\LoLAPIService;
use Monolog\Logger;
use Doctrine\ORM\EntityManager;

define('OLDER_SEASON_AVAILABLE', 3);

class SummonerService
{
    /**
     * @var \AppBundle\Services\LoLAPI\LoLAPIService
     */
    private $api;
    private $em;
    private $current_season;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct(Logger $logger, EntityManager $entityManager, LoLAPIService $api, $current_season)
    {
        $this->logger = $logger;
        $this->api = $api;
        $this->em = $entityManager;
        $this->current_season = $current_season;
    }

    public function updateSummonerInfos(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $summonerData = $this->api->getSummonerByIds($summoner->getRegion(), array($summoner->getSummonerId()));
        if ($this->api->getResponseCode() == 404) {
            $this->logger->error("Echec à la firstUpdateSummoner() du Summoner " . $summoner->getSummonerId() . " de la region " . $summoner->getRegion()->getSlug() . ", il n'existe pas dans l'API de Riot");
        }
        $summoner->setName($summonerData[$summoner->getSummonerId()]['name']);
        $summoner->setLevel($summonerData[$summoner->getSummonerId()]['summonerLevel']);
        $summoner->setProfileIconId($summonerData[$summoner->getSummonerId()]['profileIconId']);
        $date = date_create();
        date_timestamp_set($date, ($summonerData[$summoner->getSummonerId()]['revisionDate'] / 1000));
        $summoner->setRevisionDate($date);

        $this->updateRankedStats($summoner);
        $this->updateSummonerRank($summoner);
    }

    public function extraSummonerUpdate(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $this->updateSummonerInfos($summoner);
        $this->em->flush();
    }

    public function getRegionBySlug($slug)
    {
        $region = $this->em->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => $slug
        ]);
        if (empty($region)) {
            throw new Exception('Region not existing');
        }
        return $region;
    }

    public function firstUpdateSummoner(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $databaseSummoner = $this->em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'summonerId' => $summonerId,
            'region' => $region
        ]);
        if (empty($databaseSummoner)) {
            $this->logger->error("Echec à la firstUpdateSummoner() du Summoner " . $summonerId . " de la region " . $region->getSlug() . ", il n'existe pas dans la BDD");
            throw new Exception('Summoner not existing in database');
        }
        $this->updateSummonerInfos($databaseSummoner);
        $databaseSummoner->setFirstUpdated(true);
        $this->em->flush();
    }

    public function linkSummonerToUser(User $user, $summonerName, $regionSlug)
    {
        $region = $this->getRegionBySlug($regionSlug);
        $summonerName = $this->api->toSafeLowerCase($summonerName);
        $code = $user->getSummonerLinkCode();

        $summoner = $this->api->getSummonerByNames($region, array($summonerName));
        if (!isset($summoner[$summonerName]['id']))
            return 'summoner_not_found';
        $summonerId = $summoner[$summonerName]['id'];

        $masteries = $this->api->getMasteriesBySummonerIds($region, array($summonerId));
        $pageNames = array();
        foreach ($masteries[$summonerId]['pages'] as $page) {
            $pageNames[] = $page['name'];
        }
        if (in_array($code, $pageNames)) {
            // On récupère le summoner en BDD
            $summonerDatabase = $this->em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
                'id' => $summonerId,
                'region' => $region
            ]);

            // Si le summoner n'existe pas encore en BDD, on le crée
            if (empty($summonerDatabase)) {
                // TODO: Créer summoner
                $summonerData = $this->api->getSummonerByIds($region, array($summonerId));
                if ($this->api->getResponseCode() == 404) {
                    //TODO: exception summoner not found
                    throw new NotFoundHttpException('Summoner not existing');
                }
                $newSummoner = new Summoner($summonerId, $region);
                $newSummoner->setUser(null);
                $newSummoner->setName($summonerData[$summonerId]['name']);
                $newSummoner->setLevel($summonerData[$summonerId]['summonerLevel']);
                $newSummoner->setProfileIconId($summonerData[$summonerId]['profileIconId']);
                $date = date_create();
                date_timestamp_set($date, ($summonerData[$summonerId]['revisionDate'] / 1000));
                $newSummoner->setRevisionDate($date);
                $this->em->persist($newSummoner);
                $this->em->flush();
                $summonerDatabase = $newSummoner;
            } else if (!empty($summonerDatabase->getUser())) {
                return 'already_linked';
            }
            $summonerDatabase->setUser($user);

            $this->em->persist($summonerDatabase);
            $this->em->flush();

            return 'success';
        } else
            return 'page_not_found';
    }

    public function unlinkSummonerToUser(User $user, $summonerName, $regionSlug)
    {
        $region = $this->getRegionBySlug($regionSlug);
        $summonerName = $this->api->toSafeLowerCase($summonerName);
        $summoner = $this->api->getSummonerByNames($region, array($summonerName));
        if (!isset($summoner[$summonerName]['id']))
            return 'summoner_not_found';
        $summonerId = $summoner[$summonerName]['id'];
        // On récupère le summoner en BDD
        $summonerDatabase = $this->em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'id' => $summonerId,
            'region' => $region
        ]);
        $summonerDatabase->setUser(null);
        $this->em->persist($summonerDatabase);
        $this->em->flush();
        return 'success';
    }

    public function updateSummonerRank(\AppBundle\Entity\Summoner\Summoner $summoner)
    {

        $soloq = null;
        $summonerAPIData = $this->api->getLeaguesBySumonnerIdsEntry($summoner->getRegion(), array($summoner->getSummonerId()));
        $tiers = array();


        $leagueId = array();
        $divisionId = array();
        $databaseTiers = array();

        if ($this->api->getResponseCode() != 404) {
            foreach ($summonerAPIData[$summoner->getSummonerId()] as $queue) {
                switch ($queue['queue']) {
                    case 'RANKED_SOLO_5x5':
                    case 'RANKED_FLEX_SR':
                    case 'RANKED_FLEX_TT':
                        $tiers[$queue['queue']] = $queue;
                        switch ($tiers[$queue['queue']]['tier']) {
                            default:
                            case 'UNRANKED':
                                $leagueId[$queue['queue']] = Tier::UNRANKED;
                                break;
                            case 'BRONZE':
                                $leagueId[$queue['queue']] = Tier::BRONZE;
                                break;
                            case 'SILVER':
                                $leagueId[$queue['queue']] = Tier::SILVER;
                                break;
                            case 'GOLD':
                                $leagueId[$queue['queue']] = Tier::GOLD;
                                break;
                            case 'PLATINUM':
                                $leagueId[$queue['queue']] = Tier::PLATINUM;
                                break;
                            case 'DIAMOND':
                                $leagueId[$queue['queue']] = Tier::DIAMOND;
                                break;
                            case 'MASTER':
                                $leagueId[$queue['queue']] = Tier::MASTER;
                                break;
                            case 'CHALLENGER':
                                $leagueId[$queue['queue']] = Tier::CHALLENGER;
                                break;
                        }
                        switch ($tiers[$queue['queue']]['entries'][0]['division']) {
                            default:
                            case 'I':
                                $divisionId[$queue['queue']] = 1;
                                break;
                            case 'II':
                                $divisionId[$queue['queue']] = 2;
                                break;
                            case 'III':
                                $divisionId[$queue['queue']] = 3;
                                break;
                            case 'IV':
                                $divisionId[$queue['queue']] = 4;
                                break;
                            case 'V':
                                $divisionId[$queue['queue']] = 5;
                                break;
                        }

                        $databaseTiers[$queue['queue']] = $this->em->getRepository('AppBundle:Summoner\Tier')->findOneBy([
                            'league' => $leagueId[$queue['queue']],
                            'division' => $divisionId[$queue['queue']]
                        ]);
                        break;
                    default:
                        $this->logger->error("La queue " . $queue['queue'] . " a été trouvée lors de l'update du summoner " . $summoner->getName() . " avec l'id " . $summoner->getSummonerId() . " sur le serveur " . $summoner->getRegion()->getName());
                        return;
                }
            }

        } else {
            foreach (array('RANKED_SOLO_5x5', 'RANKED_FLEX_SR', 'RANKED_FLEX_TT') as $queueName) {

                $leagueId[$queueName] = Tier::UNRANKED;
                $divisionId[$queueName] = 1;
                $databaseTiers[$queueName] = $this->em->getRepository('AppBundle:Summoner\Tier')->findOneBy([
                    'league' => $leagueId[$queueName],
                    'division' => $divisionId[$queueName]
                ]);
            }
        }

        $databaseRanks = $this->getSummonerRank($summoner->getRegion(), $summoner->getSummonerId());
        foreach ($tiers as $key => $tier) {

            $isSet = false;
            switch ($tier['queue']) {
                case 'RANKED_SOLO_5x5':
                    if (isset($databaseRanks['solo'])) {
                        $isSet = true;
                        $summonerTier = $databaseRanks['solo'];
                    }
                    break;
                case 'RANKED_FLEX_SR':
                    if (isset($databaseRanks['flex5'])) {
                        $isSet = true;
                        $summonerTier = $databaseRanks['flex5'];
                    }
                    break;
                case 'RANKED_FLEX_TT':
                    if (isset($databaseRanks['flex3'])) {
                        $isSet = true;
                        $summonerTier = $databaseRanks['flex3'];
                    }
                    break;
            }

            if (!$isSet) {
                $summonerTier = new summonerTiers();

                switch ($tier['queue']) {
                    case 'RANKED_SOLO_5x5':
                        $summonerTier->setQueueId(summonerTiers::SOLO_DUO);
                        break;
                    case 'RANKED_FLEX_SR':
                        $summonerTier->setQueueId(summonerTiers::FLEX_5v5);
                        break;
                    case 'RANKED_FLEX_TT':
                        $summonerTier->setQueueId(summonerTiers::FLEX_3v3);
                        break;
                }

                $summonerTier->setSummoner($summoner);
            }

            $summonerTier->setRegionId($summoner->getRegion()->getId());
            $summonerTier->setTier($databaseTiers[$key]);

            if ($databaseTiers[$key]->getLeague() != Tier::UNRANKED) {
                $summonerTier->setLeaguePoints($tier['entries'][0]['leaguePoints']);
                $summonerTier->setWins($tier['entries'][0]['wins']);
                $summonerTier->setLosses($tier['entries'][0]['losses']);
                $summonerTier->setFreshBlood($tier['entries'][0]['isFreshBlood']);
                $summonerTier->setHotStreak($tier['entries'][0]['isHotStreak']);
                $summonerTier->setInactive($tier['entries'][0]['isInactive']);
                $summonerTier->setVeteran($tier['entries'][0]['isVeteran']);
                // Mini series
                if (isset($tier['entries'][0]['miniSeries'])) {
                    $summonerTier->setMiniSeries($tier['entries'][0]['miniSeries']['progress']);
                }
            }
            if (!$isSet) {
                $summoner->addTier($summonerTier);
            }
        }
    }

    // TODO: plus d'appel à l'api mais à la BDD
    public function getSummonerRank(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $databaseSummoner = $this->em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'summonerId' => $summonerId,
            'region' => $region->getId()
        ]);
        $summonerRanks = $this->em->getRepository('AppBundle:Summoner\SummonerTiers')->findBy([
            'summoner' => $databaseSummoner,
            'regionId' => $region->getId()
        ]);
        $ranks = array();
        foreach ($summonerRanks as $rank) {
            switch ($rank->getQueueId()) {
                case 1:
                    $ranks['solo'] = $rank;
                    break;
                case 2;
                    $ranks['flex5'] = $rank;
                    break;
                case 3:
                    $ranks['flex3'] = $rank;
                    break;
            }
        }

        return $ranks;
    }

    /*
    public function getSummonerRank(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $soloq = null;
        $summoner = $this->api->getLeaguesBySumonnerIdsEntry($region, array($summonerId));
        if ($this->api->getResponseCode() == 404)
            return null;
        foreach ($summoner[$summonerId] as $queue) {
            switch ($queue['queue']) {
                case 'RANKED_SOLO_5x5':
                    $soloq = $queue;
                    break;
                case 'RANKED_TEAM_3x3':
                    break;
                case 'RANKED_TEAM_5x5':
                    break;
            }
        }
        return $soloq;
    }*/

    public function updateRankedStats(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        for ($season = OLDER_SEASON_AVAILABLE; $season <= $this->current_season; $season++) {
            $rankedStatsData = $this->api->getRankedStatsBySummonerId($summoner->getRegion(), $summoner->getSummonerId(), $season);
            if ($this->api->getResponseCode() !== 404) {
                foreach ($rankedStatsData['champions'] as $championData) {
                    $championRankedStats = $this->em->getRepository('AppBundle:Summoner\RankedStats')->findOneBy([
                        'summonerId' => $summoner->getSummonerId(),
                        'regionId' => $summoner->getRegion()->getId(),
                        'season' => $season,
                        'championId' => $championData['id']
                    ]);
                    $firstUpdate = false;
                    if (empty($championRankedStats)) {
                        $firstUpdate = true;
                    }
                    if ($firstUpdate) {
                        $championRankedStats = new rankedStats($summoner->getSummonerId(), $summoner->getRegion()->getId(), $season, $championData['id']);
                    }
                    $championRankedStats->setKills($championData['stats']['totalChampionKills']);
                    $championRankedStats->setDeaths($championData['stats']['totalDeathsPerSession']);
                    $championRankedStats->setAssists($championData['stats']['totalAssists']);
                    $championRankedStats->setWins($championData['stats']['totalSessionsWon']);
                    $championRankedStats->setLoses($championData['stats']['totalSessionsLost']);
                    $championRankedStats->setCreeps($championData['stats']['totalMinionKills']);
                    if ($firstUpdate) {
                        $this->em->persist($championRankedStats);
                    }
                }
            }
        }
    }

    public function getRankedStats(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $merged = array();
        for ($season = OLDER_SEASON_AVAILABLE; $season <= $this->current_season; $season++) {
            $rankedStatsData = $this->em->getRepository('AppBundle:Summoner\RankedStats')->findBy([
                'summonerId' => $summoner->getSummonerId(),
                'regionId' => $summoner->getRegion()->getId(),
                'season' => $season
            ]);
            if (!empty($rankedStatsData)) {
                $merged[$season]['average'] = $rankedStatsData[0];
                unset($rankedStatsData[0]);
                $merged[$season]['champions'] = $rankedStatsData;
            }
        }
        return $merged;
    }

    public function updateChampionsMastery($summonerId, \AppBundle\Entity\StaticData\Region $region)
    {
        $championsMasteryData = $this->api->getChampionsMastery($region, $summonerId);
        if ($this->api->getResponseCode() !== 404) {
            foreach ($championsMasteryData as $championData) {
                $championMastery = $this->em->getRepository('AppBundle:Summoner\ChampionMastery')->findOneBy([
                    'summonerId' => $summonerId,
                    'regionId' => $region->getId(),
                    'championId' => $championData['championId']
                ]);
                if (empty($championMastery)) {
                    $championMastery = new ChampionMastery($summonerId, $region->getId(), $championData['championId']);
                }
                $championMastery->setLevel($championData['championLevel']);
                $championMastery->setPoints($championData['championPoints']);
                $championMastery->setChestGranted(boolval($championData['chestGranted']));
                $championMastery->setPointsUntilNextLevel($championData['championPointsUntilNextLevel']);
                $championMastery->setTokensEarned($championData['tokensEarned']);
                $date = date_create();
                date_timestamp_set($date, ($championData['lastPlayTime'] / 1000));
                $championMastery->setLastPlayTime($date);
                $this->em->persist($championMastery);
            }
            $this->em->flush();
        }
        return $this->getChampionsMastery($summonerId, $region);
    }

    public function getChampionsMastery($summonerId, \AppBundle\Entity\StaticData\Region $region)
    {
        $merged = array();

        $championsMasteryData = $this->em->getRepository('AppBundle:Summoner\ChampionMastery')->findBy([
            'summonerId' => $summonerId,
            'regionId' => $region->getId()
        ],
            [
                //TODO: il faut classer par level ensuite points ensuite nom
                'level' => 'DESC',
                'points' => 'DESC'
            ]);

        // Switch du 1er et 2eme
        $topChampionsMastery = array($championsMasteryData[0], $championsMasteryData[1], $championsMasteryData[2]);
        $tempChampMastery = $topChampionsMastery[0];
        $topChampionsMastery[0] = $topChampionsMastery[1];
        $topChampionsMastery[1] = $tempChampMastery;

        $merged['top'] = $topChampionsMastery;
        unset($championsMasteryData[2]);
        unset($championsMasteryData[1]);
        unset($championsMasteryData[0]);
        $merged['remaining'] = $championsMasteryData;

        return $merged;
    }

    public function updateMasteryPages($summonerId, \AppBundle\Entity\StaticData\Region $region)
    {
        $masteryPagesData = $this->api->getMasteriesBySummonerIds($region, array($summonerId));
        if ($this->api->getResponseCode() !== 404) {
            $pageNum = 1;
            foreach ($masteryPagesData as $pageData) {
                $championMastery = $this->em->getRepository('AppBundle:Summoner\MasteryPage')->findOneBy([
                    'summonerId' => $summonerId,
                    'regionId' => $region->getId(),
                    'pageId' => $pageNum,
                    'pageId' => $pageNum,
                ]);
                if (empty($championMastery)) {
                    $championMastery = new ChampionMastery($summonerId, $region->getId(), $pageData['championId']);
                }
                $championMastery->setLevel($pageData['championLevel']);
                $championMastery->setPoints($pageData['championPoints']);
                $championMastery->setChestGranted(boolval($pageData['chestGranted']));
                $championMastery->setPointsUntilNextLevel($pageData['championPointsUntilNextLevel']);
                $championMastery->setTokensEarned($pageData['tokensEarned']);
                $date = date_create();
                date_timestamp_set($date, ($pageData['lastPlayTime'] / 1000));
                $championMastery->setLastPlayTime($date);
                $this->em->persist($championMastery);
                $pageNum++;
            }
            $this->em->flush();
        }
        return $this->getChampionsMastery($summonerId, $region);
    }

    public function getMasteryPages($summonerId, \AppBundle\Entity\StaticData\Region $region)
    {
        $masteryPagesData = $this->em->getRepository('AppBundle:Summoner\MasteryPage')->findBy([
            'summonerId' => $summonerId,
            'regionId' => $region->getId()
        ],
            [
                'pageId' => 'ASC'
            ]);

        return $masteryPagesData;
    }

    public function getRunePages(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $runePagesData = $this->api->getRunesBySummonerIds($summoner->getRegion(), array($summoner->getSummonerId()));
        $images = array();
        $runeData = array();
        foreach ($runePagesData[$summoner->getSummonerId()]['pages'] as $page) {
            if (isset($page['slots'])) {
                foreach ($page['slots'] as $rune) {
                    $runeData['runeId'] = $this->em->getRepository('AppBundle:StaticData\Rune')->findOneBy([
                        'id' => $rune['runeId']
                    ]);
                    $images[$rune['runeId']] = $runeData['runeId']->getImage();
                }
            }
        }
        return array(
            'images' => $images,
            'data' => $runePagesData[$summoner->getSummonerId()]
        );
    }

    public function getMasteriesPages(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $masteriesPagesData = $this->api->getMasteriesBySummonerIds($summoner->getRegion(), array($summoner->getSummonerId()));
        $pages = array();
        foreach ($masteriesPagesData[$summoner->getSummonerId()]['pages'] as $page) {
            $index = count($pages);
            if (isset($page['name'])) {
                $pages[$index]['name'] = $page['name'];
            } else {
                $pages[$index]['name'] = '';
            }

            $pages[$index]['current'] = $page['current'];
            $pages[$index]['masteries'] = array();
            if (isset($page['masteries'])) {
                foreach ($page['masteries'] as $mastery) {
                    $pages[$index]['masteries'][$mastery['id']] = $mastery['rank'];
                }
            }
        }
        return $pages;
    }

    public function getMatchHistory(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $recentGamesData = $this->api->getRecentGames($summoner->getRegion(), $summoner->getSummonerId());
        return $recentGamesData;
    }

    public function getRunePageByData(\AppBundle\Entity\StaticData\Region $region, array $runePagesData, \AppBundle\Entity\Language $language)
    {
        $images = array();
        $ids = array();
        $runeData = array();
        $stats = array();
        foreach ($runePagesData as $participant) {
            foreach ($participant['runes'] as $rune) {
                $ids[$rune['runeId']] = $rune['runeId'];
            }
        }
        foreach ($ids as $id) {
            $runeData['runeId'] = $this->em->getRepository('AppBundle:StaticData\Rune')->findOneBy([
                'id' => $id
            ]);
            $images[$id] = $runeData['runeId']->getImage();
            $stats[$id] = $this->em->getRepository('AppBundle:StaticData\Translation\RuneTranslation')->findOneBy([
                'runeId' => $id,
                'languageId' => $language->getId()
            ]);
        }
        //$toto = $this->em->getRepository('AppBundle:StaticData\Rune')->findAllIn($ids);
        return array(
            'images' => $images,
            'stats' => $stats
        );
    }

    public function getRunePagesInfo(\AppBundle\Entity\StaticData\Region $region, array $data)
    {
        $ids = array();
        $stats = array();
        foreach ($data['pages'] as $page) {
            if (isset($page['slots'])) {
                foreach ($page['slots'] as $rune) {
                    $ids[$rune['runeId']] = $rune['runeId'];
                }
            }
        }
        foreach ($ids as $id) {
            //TODO: chercher les infos des runes directement depuis la BDD
            $stats[$id] = $this->api->getStaticRuneById($region, $id, 'fr_FR');
        }
        return $stats;
    }

    public function getSummonerSpellsSortedById(\AppBundle\Entity\StaticData\Region $region)
    {
        $sumonnerSpellsData = $this->api->getStaticSummonerSpells($region);
        $summonerSpells = array();
        foreach ($sumonnerSpellsData["data"] as $sumonnerSpell) {
            $summonerSpells[$sumonnerSpell["id"]] = $sumonnerSpell["key"];
        }
        return $summonerSpells;
    }

    public function getSummonerNamesByIds(\AppBundle\Entity\StaticData\Region $region, array $ids)
    {
        $size = count($ids);
        $names = array();
        if ($size > 40) {
            for ($i = 0; $i < ($size / 40); $i++) {
                $slice = array_slice($ids, $i * 40, 40, true);
                $temp_names[$i] = $this->api->getNamesBySummonerIds($region, $slice);
                foreach ($temp_names[$i] as $id => $name) {
                    $names[$id] = $name;
                }
            }
        } else {
            $names = $this->api->getNamesBySummonerIds($region, $ids);
        }
        return $names;
    }

    //TODO: faire un même fonction qui va chercher seulement quelques items a partir d'un tableau d'id
    //TODO: Il faudrait carrément faire des requêtes pour les récupérer en tableau d'ID
    public function getItemsSortedById()
    {
        $items = $this->em->getRepository('AppBundle:StaticData\Item')->findAll();
        $sorted = array();
        foreach ($items as $item) {
            $sorted[$item->getId()] = $item;
        }
        return $sorted;
    }

    public function getLiveGame(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $currentGame = $this->api->getCurrentGame($summoner->getRegion(), $summoner->getSummonerId());

        $summonerSpells = $this->getSummonerSpellsSortedById($summoner->getRegion());
        $liveGame = array();
        if (isset($currentGame['participants'])) {
            //var_dump($currentGame['participants'] );exit();
            foreach ($currentGame['participants'] as $participant) {
                $lg_soloq = $this->getSummonerRank($summoner->getRegion(), $participant['summonerId']);

                if (!isset($lg_soloq['solo'])) {
                    $lg_soloqimg = "unranked_";
                    $liveGame[$participant['summonerId']]['rank'] = 'Unranked';
                } else {
                    $lg_soloq = $lg_soloq['solo'];
                    $lg_soloqimg = $lg_soloq->getTier()->getImage();
                    $liveGame[$participant['summonerId']]['rank'] = $lg_soloq->getTier()->getName();
                }
                $liveGame[$participant['summonerId']]['img'] = $lg_soloqimg;
            }
        }
        $data['live_game'] = $liveGame;
        $data['currentGame'] = $currentGame;
        $data['summonerSpells'] = $summonerSpells;
        return $data;
    }

    public function getChampionsSortedByIds(\AppBundle\Entity\Language $language)
    {
        $champions = $this->em->getRepository('AppBundle:StaticData\Champion')->findAll();
        $translates = $this->em->getRepository('AppBundle:StaticData\Translation\ChampionTranslation')->findBy([
            'languageId' => $language->getId()
        ]);
        $temp = array();
        foreach ($champions as $champion) {
            $temp[$champion->getId()] = array('key' => $champion->getKey());
        }
        foreach ($translates as $translate) {
            $temp[$translate->getChampionId()]['name'] = $translate->getName();
            $temp[$translate->getChampionId()]['title'] = $translate->getTitle();
        }
        return $temp;
    }

    public function getLanguageByRequestLocale(\Symfony\Component\HttpFoundation\Request $request)
    {
        $language = $this->em->getRepository('AppBundle:Language')->findOneBy([
            'symfonyLocale' => $request->getLocale()
        ]);
        return $language;
    }

    public function getSummonerByNameForAllRegions($name)
    {
        $regions = $this->em->getRepository('AppBundle:StaticData\Region')->findAll();
        $data = array();
        $summoners = array();
        foreach ($regions as $region) {
            $summoner = $this->api->getSummonerByNames($region, array($name));
            if ($this->api->getResponseCode() != 404) {
                $summoners[$region->getSlug()] = $summoner;
            }
        }
        $data['regions'] = $regions;
        $data['summoners'] = $summoners;
        return $data;
    }
}
