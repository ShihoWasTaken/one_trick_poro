<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\Summoner\Summoner;
use AppBundle\Entity\Summoner\RankedStats;
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
        $summoner = $this->api->getLeaguesBySumonnerIdsEntry(array($summonerId));
        if(isset($summoner['errorCode']) && ($summoner['errorCode'] == 404))
            // Pas de league trouvée
            return null;
        else
        {
            $soloq = null;
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
            return $soloq;
        }
    }

    public function updateRankedStats($summonerId)
    {
        for($season = OLDER_SEASON_AVAILABLE; $season < ACTUAL_SEASON; $season++)
        {
            $rankedStatsData = $this->api->getRankedStatsBySummonerId($summonerId, $season);
            if($this->api->getResponseCode() !== 404)
            {
                foreach($rankedStatsData['champions'] as $championData)
                {
                    $championRankedStats = $this->em->getRepository('AppBundle:Summoner\RankedStats')->findOneBy([
                        'summonerId' => $summonerId,
                        'season' => $season,
                        'championId' => $championData['id']
                    ]);
                    if ($championRankedStats == null)
                    {
                        $championRankedStats = new rankedStats($summonerId, $season, $championData['id']);
                    }
                    $championRankedStats->setPlayedGames($championData['stats']['totalSessionsPlayed']);
                    $championRankedStats->setKills($championData['stats']['totalChampionKills']);
                    $championRankedStats->setDeaths($championData['stats']['totalDeathsPerSession']);
                    $championRankedStats->setAssists($championData['stats']['totalAssists']);
                    $championRankedStats->setWins($championData['stats']['totalSessionsWon']);
                    $championRankedStats->setLoses($championData['stats']['totalSessionsLost']);
                    $championRankedStats->setWinrate(round(($championData['stats']['totalSessionsWon'] / $championData['stats']['totalSessionsPlayed'] * 100 ), 2));
                    $championRankedStats->setCreeps($championData['stats']['totalMinionKills']);
                    $this->em->persist($championRankedStats);
                }
            }
        }
        $this->em->flush();
        return $this->getRankedStats($summonerId);
    }

    public function getRankedStats($summonerId)
    {
        $data = array();
        for($season = OLDER_SEASON_AVAILABLE; $season < ACTUAL_SEASON; $season++)
        {
            $rankedStatsData = $this->em->getRepository('AppBundle:Summoner\RankedStats')->findBy([
                'summonerId' => $summonerId,
                'season' => $season
            ]);
            $data[$season] = $rankedStatsData;
        }
        return $data;
    }
}
