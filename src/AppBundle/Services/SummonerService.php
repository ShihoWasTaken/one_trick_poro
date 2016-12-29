<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\Summoner\Summoner;
use AppBundle\Entity\Summoner\RankedStats;
use AppBundle\Entity\Summoner\ChampionMastery;
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

        $em = $this->container->get('doctrine')->getManager();
        // On ajoute au User
        //$user->addSummoner($summoner);

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

        $em->persist($newSummoner);
        $em->flush();
        return $newSummoner;
    }

    public function getSummonerRank($summonerId)
    {
        $soloq = null;
        $summoner = $this->api->getLeaguesBySumonnerIdsEntry(array($summonerId));
        if(!isset($summoner['errorCode']))
        {
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
                /*
                echo $queue['queue'] . '<br>';
                echo $queue['entries'][0]['wins'] . '<br>';
                echo $queue['tier'] . '<br>';
                echo $queue['name'] . '<br>';
                echo $queue['entries'][0]['division'] . '<br>';
                echo $queue['entries'][0]['leaguePoints'] . '<br>';
                echo $queue['entries'][0]['losses'] . '<br>';
                echo $queue['entries'][0]['playerOrTeamName'] . '<br>';
                echo '<br>';
                */
            }
        }
        return $soloq;
    }

    public function updateRankedStats($summoner)
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
}
