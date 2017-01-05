<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\Summoner\Summoner;
use AppBundle\Entity\Summoner\RankedStats;
use AppBundle\Entity\Summoner\ChampionMastery;
use AppBundle\Entity\Summoner\Tier;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Services\CurlHttpException;
use AppBundle\Services\LoLAPI\LoLAPIService;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

define('OLDER_SEASON_AVAILABLE', 3);
define('ACTUAL_SEASON', 7);

class SummonerService
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

    public function getRegionBySlug($region)
    {
        $region = $this->em->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => $region
        ]);
        if($region == null)
        {
            throw new Exception('Region not existing');
        }
        return $region;
    }

    public function firstUpdateSummoner(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $databaseSummoner = $this->em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'id' => $summonerId,
            'region' => $region
        ]);
        if (empty($databaseSummoner))
        {
            throw new Exception('Summoner not existing in database');
        }
        $summonerData = $this->api->getSummonerByIds(array($summonerId));
        if($this->api->getResponseCode() == 404)
        {
            throw new Exception('Summoner not existing in Riot Games Database');
        }
        $databaseSummoner->setName($summonerData[$summonerId]['name']);
        $databaseSummoner->setLevel($summonerData[$summonerId]['summonerLevel']);
        $databaseSummoner->setProfileIconId($summonerData[$summonerId]['profileIconId']);
        $date = date_create();
        date_timestamp_set($date, ($summonerData[$summonerId]['revisionDate']/1000));
        $databaseSummoner->setRevisionDate($date);

        $this->updateRankedStats($databaseSummoner);
        $this->updateSummonerRank($databaseSummoner);
        $databaseSummoner->setFirstUpdated(true);
        $this->em->persist($databaseSummoner);
        $this->em->flush();
    }

    public function linkSummonerToUser(User $user, $summonerName)
    {
        $summonerName = strtolower($summonerName);
        //$code = 'LeagueOfTools-' . $user->getId();
        $code = 'Ahri';

        $summoner = $this->api->getSummonerByNames(array($summonerName));
        if(!isset($summoner[$summonerName]['id']))
            return 'summoner_not_found';
        $summonerId = $summoner[$summonerName]['id'];

        $masteries = $this->api->getMasteriesBySummonerIds(array($summonerId));
        $pageNames = array();
        foreach($masteries[$summonerId]['pages'] as $page)
        {
            $pageNames[] = $page['name'];
        }
        if (in_array($code, $pageNames))
        {
            $em = $this->container->get('doctrine')->getManager();
            // On ajoute au User
            //$user->addSummoner($summoner);

            $newSummoner = new Summoner();
            $newSummoner->setUser($user);
            $newSummoner->setRegion('euw');
            $newSummoner->setSummonerId($summonerId);
            $newSummoner->setName($summoner[$summonerName]['name']);
            $newSummoner->setLevel($summoner[$summonerName]['summonerLevel']);
            $newSummoner->setProfileIconId($summoner[$summonerName]['profileIconId']);
            $date = date_create();
            date_timestamp_set($date, ($summoner[$summonerName]['revisionDate']/1000));
            $newSummoner->setRevisionDate($date);

            $em->persist($newSummoner);
            $em->flush();

            return 'success';
        }
        else
            return 'page_not_found';
    }

    public function createSummoner($region, $summonerId)
    {

        $summoner = $this->api->getSummonerByIds(array($summonerId));
        if(!isset($summoner[$summonerId]['id']))
        {
            return 'summoner_not_found';
        }
        $summonerId = $summoner[$summonerId]['id'];

        $newSummoner = new Summoner();
        $newSummoner->setUser(null);
        $newSummoner->setRegion($region);
        $newSummoner->setSummonerId($summonerId);
        $newSummoner->setName($summoner[$summonerId]['name']);
        $newSummoner->setLevel($summoner[$summonerId]['summonerLevel']);
        $newSummoner->setProfileIconId($summoner[$summonerId]['profileIconId']);
        $date = date_create();
        date_timestamp_set($date, ($summoner[$summonerId]['revisionDate']/1000));
        $newSummoner->setRevisionDate($date);

        $this->em->persist($newSummoner);
        $this->em->flush();
        return $newSummoner;
    }
    
    public function updateSummonerRank(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        
        $soloq = null;
        $summonerAPIData = $this->api->getLeaguesBySumonnerIdsEntry(array($summoner->getId()));
        if($this->api->getResponseCode() != 404)
        {
            foreach($summonerAPIData[$summoner->getId()] as $queue)
            {
                switch($queue['queue'])
                {
                    case 'RANKED_SOLO_5x5':
                        $soloq = $queue;
                        break;
                    case 'RANKED_TEAM_3x3':
                        break;
                    case 'RANKED_TEAM_5x5':
                        break;
                }
            }
        }
        else
        {
            return null;
        }
        switch($soloq['tier'])
        {
            default:
            case 'UNRANKED':
                $leagueId = Tier::UNRANKED;
                break;
            case 'BRONZE':
                $leagueId = Tier::BRONZE;
                break;
            case 'SILVER':
                $leagueId = Tier::SILVER;
                break;
            case 'GOLD':
                $leagueId = Tier::GOLD;
                break;
            case 'PLATINUM':
                $leagueId = Tier::PLATINUM;
                break;
            case 'DIAMOND':
                $leagueId = Tier::DIAMOND;
                break;
            case 'MASTER':
                $leagueId = Tier::MASTER;
                break;
            case 'CHALLENGER':
                $leagueId = Tier::CHALLENGER;
                break;
        }
        switch($soloq['entries'][0]['division'])
        {
            default:
            case 'I':
                $divisionId = 1;
                break;
            case 'II':
                $divisionId = 2;
                break;
            case 'III':
                $divisionId = 3;
                break;
            case 'IV':
                $divisionId = 4;
                break;
            case 'V':
                $divisionId = 5;
                break;
        }

        $databaseTier = $this->em->getRepository('AppBundle:Summoner\Tier')->findOneBy([
            'league' => $leagueId,
            'division' => $divisionId
        ]);
        $summoner->setTier($databaseTier);
        $this->em->persist($summoner);
        $this->em->flush();
    }    

    public function getSummonerRank($summonerId)
    {
        $soloq = null;
        $summoner = $this->api->getLeaguesBySumonnerIdsEntry(array($summonerId));
        if($this->api->getResponseCode() == 404)
            return null;
        foreach($summoner[$summonerId] as $queue)
        {
            switch($queue['queue'])
            {
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
    }

    public function updateRankedStats(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        for($season = OLDER_SEASON_AVAILABLE; $season <= ACTUAL_SEASON; $season++)
        {
            $rankedStatsData = $this->api->getRankedStatsBySummonerId($summoner->getId(), $season);
            if($this->api->getResponseCode() !== 404)
            {
                foreach($rankedStatsData['champions'] as $championData)
                {
                    $championRankedStats = $this->em->getRepository('AppBundle:Summoner\RankedStats')->findOneBy([
                        'summonerId' => $summoner->getId(),
                        'regionId' => $summoner->getRegion()->getId(),
                        'season' => $season,
                        'championId' => $championData['id']
                    ]);
                    if ($championRankedStats == null)
                    {
                        $championRankedStats = new rankedStats($summoner->getId(), $summoner->getRegion()->getId(), $season, $championData['id']);
                    }
                    $playedGames = $championData['stats']['totalSessionsPlayed'];
                    $championRankedStats->setPlayedGames($playedGames);
                    $championRankedStats->setKills($championData['stats']['totalChampionKills']);
                    $championRankedStats->setDeaths($championData['stats']['totalDeathsPerSession']);
                    $championRankedStats->setAssists($championData['stats']['totalAssists']);
                    $championRankedStats->setWins($championData['stats']['totalSessionsWon']);
                    $championRankedStats->setLoses($championData['stats']['totalSessionsLost']);
                    $championRankedStats->setCreeps($championData['stats']['totalMinionKills']);
                    $this->em->persist($championRankedStats);
                }
            }
        }
        $this->em->flush();
        return $this->getRankedStats($summoner);
    }

    public function getRankedStats($summoner)
    {
        $merged = array();
        for($season = OLDER_SEASON_AVAILABLE; $season <= ACTUAL_SEASON; $season++)
        {
            $rankedStatsData = $this->em->getRepository('AppBundle:Summoner\RankedStats')->findBy([
                'summonerId' => $summoner->getId(),
                'regionId' => $summoner->getRegion()->getId(),
                'season' => $season
            ]);
            if(!empty($rankedStatsData))
            {
                $merged[$season]['average'] = $rankedStatsData[0];
                unset($rankedStatsData[0]);
                $merged[$season]['champions'] = $rankedStatsData;
            }
        }
        return $merged;
    }

    public function updateChampionsMastery($summonerId, \AppBundle\Entity\StaticData\Region $region)
    {
        $championsMasteryData = $this->api->getChampionsMastery($summonerId);
        if($this->api->getResponseCode() !== 404)
        {
            foreach($championsMasteryData as $championData)
            {
                $championMastery = $this->em->getRepository('AppBundle:Summoner\ChampionMastery')->findOneBy([
                    'summonerId' => $summonerId,
                    'regionId' => $region->getId(),
                    'championId' => $championData['championId']
                ]);
                if (empty($championMastery))
                {
                    $championMastery = new ChampionMastery($summonerId, $region->getId(), $championData['championId']);
                }
                $championMastery->setLevel($championData['championLevel']);
                $championMastery->setPoints($championData['championPoints']);
                $championMastery->setChestGranted(boolval($championData['chestGranted']));
                $championMastery->setPointsUntilNextLevel($championData['championPointsUntilNextLevel']);
                $championMastery->setTokensEarned($championData['tokensEarned']);
                $date = date_create();
                date_timestamp_set($date, ($championData['lastPlayTime']/1000));
                $championMastery->setLastPlayTime($date);
                $this->em->persist($championMastery);
            }
            $this->em->flush();
        }
        return $this->getChampionsMastery($summonerId, $region);
        /*
        $topChampionsMastery = $api->getMasteryTopChampions($summonerId);
        for($i = 0; $i < count($topChampionsMastery); $i++)
        {
            $arr = array('championKey' => $temp[$topChampionsMastery[$i]['championId']]['key']);
            $topChampionsMastery[$i] = array_merge($topChampionsMastery[$i], $arr);
        }
        // Switch du 1er et 2eme
        $tempChampMastery = $topChampionsMastery[0];
        $topChampionsMastery[0] = $topChampionsMastery[1];
        $topChampionsMastery[1] = $tempChampMastery;*/
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
        $masteryPagesData = $this->api->getMasteriesBySummonerIds(array($summonerId));
        if($this->api->getResponseCode() !== 404)
        {
            $pageNum = 1;
            foreach($masteryPagesData as $pageData)
            {
                $championMastery = $this->em->getRepository('AppBundle:Summoner\MasteryPage')->findOneBy([
                    'summonerId' => $summonerId,
                    'regionId' => $region->getId(),
                    'pageId' => $pageNum,
                    'pageId' => $pageNum,
                ]);
                if (empty($championMastery))
                {
                    $championMastery = new ChampionMastery($summonerId, $region->getId(), $pageData['championId']);
                }
                $championMastery->setLevel($pageData['championLevel']);
                $championMastery->setPoints($pageData['championPoints']);
                $championMastery->setChestGranted(boolval($pageData['chestGranted']));
                $championMastery->setPointsUntilNextLevel($pageData['championPointsUntilNextLevel']);
                $championMastery->setTokensEarned($pageData['tokensEarned']);
                $date = date_create();
                date_timestamp_set($date, ($pageData['lastPlayTime']/1000));
                $championMastery->setLastPlayTime($date);
                $this->em->persist($championMastery);
                $pageNum++;
            }
            $this->em->flush();
        }
        return $this->getChampionsMastery($summonerId, $region);
        /*
        $topChampionsMastery = $api->getMasteryTopChampions($summonerId);
        for($i = 0; $i < count($topChampionsMastery); $i++)
        {
            $arr = array('championKey' => $temp[$topChampionsMastery[$i]['championId']]['key']);
            $topChampionsMastery[$i] = array_merge($topChampionsMastery[$i], $arr);
        }
        // Switch du 1er et 2eme
        $tempChampMastery = $topChampionsMastery[0];
        $topChampionsMastery[0] = $topChampionsMastery[1];
        $topChampionsMastery[1] = $tempChampMastery;*/
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
        $runePagesData = $this->api->getRunesBySummonerIds(array($summoner->getId()));
        $images = array();
        $runeData = array();
        foreach($runePagesData[$summoner->getId()]['pages'] as $page)
        {
            if(isset($page['slots']))
            {
                foreach($page['slots'] as $rune)
                {
                    $runeData['runeId'] = $this->em->getRepository('AppBundle:StaticData\Rune')->findOneBy([
                        'id' => $rune['runeId']
                    ]);
                    $images[$rune['runeId']] = $runeData['runeId']->getImage();
                }
            }
        }
        return array(
            'images' => $images,
            'data' => $runePagesData[$summoner->getId()]
        );
    }

    public function getRunePageByData(array $runePagesData)
    {
        $images = array();
        $ids = array();
        $runeData = array();
        $stats = array();
        foreach($runePagesData as $participant)
        {
            foreach($participant['runes'] as $rune)
            {
                $ids[$rune['runeId']] = $rune['runeId'];
            }
        }
        foreach($ids as $id)
        {
            $runeData['runeId'] = $this->em->getRepository('AppBundle:StaticData\Rune')->findOneBy([
                'id' => $id
            ]);
            $images[$id] = $runeData['runeId']->getImage();
            $stats[$id] = $this->api->getStaticRuneById($id,'fr_FR');
        }
        return array(
            'images' => $images,
            'stats' => $stats
        );
    }

    public function getRunePagesInfo(array $data)
    {
        $ids = array();
        $stats = array();
        foreach($data['pages'] as $page)
        {
            if(isset($page['slots']))
            {
                foreach($page['slots'] as $rune)
                {
                    $ids[$rune['runeId']] = $rune['runeId'];
                }
            }
        }
        foreach($ids as $id)
        {
            //TODO: chercher les infos des runes directement depuis la BDD
            $stats[$id] = $this->api->getStaticRuneById($id,'fr_FR');
        }
        return $stats;
    }

    public function getLiveGame(\AppBundle\Entity\Summoner\Summoner $summoner)
    {
        $currentGame = $this->api->getCurrentGame($summoner->getId());

        $sumonnerSpellsData = $this->api->getStaticSummonerSpells();
        $summonerSpells = array();
        foreach($sumonnerSpellsData["data"] as $sumonnerSpell)
        {
            $summonerSpells[$sumonnerSpell["id"]] = $sumonnerSpell["key"];
        }
        $liveGame = array();
        if(isset($currentGame['participants']))
        {
            //var_dump($currentGame['participants'] );exit();
            foreach($currentGame['participants'] as $participant)
            {
                $lg_soloq = $this->getSummonerRank($participant['summonerId']);
                if(!isset($lg_soloq))
                {
                    $lg_soloqimg = "unranked_";
                    $liveGame[$participant['summonerId']]['rank'] = 'Unranked';
                }
                else
                {
                    $lg_soloqimg = strtolower($lg_soloq['tier']) . '_' . $lg_soloq['entries'][0]['division'];
                    $liveGame[$participant['summonerId']]['rank'] = $lg_soloq['tier'] . ' ' . $lg_soloq['entries'][0]['division'];
                }
                $liveGame[$participant['summonerId']]['img'] = $lg_soloqimg;
            }
        }
        $data['live_game'] = $liveGame;
        $data['currentGame'] = $currentGame;
        $data['summonerSpells'] = $summonerSpells;
        return $data;
    }

    public function getChampionsSortedByIds()
    {
        $champions = $this->em->getRepository('AppBundle:StaticData\Champion')->findAll();
        $temp = array();
        foreach($champions as $champion)
        {
            $temp[$champion->getId()] = array('key' => $champion->getKey());
        }
        return $temp;
    }
}
