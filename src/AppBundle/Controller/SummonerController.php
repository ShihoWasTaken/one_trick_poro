<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Summoner\Summoner;
use AppBundle\Repository\Summoner\SummonerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SummonerController extends Controller
{   
    public function indexAction($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $static_data_version = $this->container->getParameter('static_data_version');

        $safeRegion = $em->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => $region
        ]);
        if($safeRegion == null)
        {
            //TODO: lancer exception
            throw new NotFoundHttpException('Region not existing');
        }

        // On récupère le summoner en BDD
        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'id' => $summonerId,
            'region' => $safeRegion
        ]);

        // Si le summoner n'existe pas encore en BDD, on le crée
        if ($summoner == null)
        {
            $summoner = $api->getSummonerByIds(array($summonerId));
            if($api->getResponseCode() == 404)
            {
                //TODO: exception summoner not found
                throw new NotFoundHttpException('Summoner not existing');
            }
            $summonerId = $summoner[$summonerId]['id'];

            $newSummoner = new Summoner($summonerId, $safeRegion);
            $newSummoner->setUser(null);
            $newSummoner->setName($summoner[$summonerId]['name']);
            $newSummoner->setLevel($summoner[$summonerId]['summonerLevel']);
            $newSummoner->setProfileIconId($summoner[$summonerId]['profileIconId']);
            $date = date_create();
            date_timestamp_set($date, ($summoner[$summonerId]['revisionDate']/1000));
            $newSummoner->setRevisionDate($date);

            $em->persist($newSummoner);
            $em->flush();
            $summoner = $newSummoner;
        }
        $rankedStats = $sum->updateRankedStats($summoner);

        $soloq = $sum->getSummonerRank($summonerId);
        if(!isset($soloq))
        {
            $soloqimg = "unranked_";
        }
        else
        {
            $soloqimg = strtolower($soloq['tier']) . '_' . $soloq['entries'][0]['division'];
        }

        $champions = $em->getRepository('AppBundle:StaticData\Champion')->findAll();
        $temp = array();
        foreach($champions as $champion)
        {
            $temp[$champion->getId()] = array('key' => $champion->getKey());
        }
        
        $championsMastery = $sum->updateChampionsMastery($summonerId, $safeRegion);
        
        $currentGame = $api->getCurrentGame($summonerId);

        $sumonnerSpellsData = $api->getStaticSummonerSpells();
        $summonerSpells = array();
        foreach($sumonnerSpellsData["data"] as $sumonnerSpell)
        {
            $summonerSpells[$sumonnerSpell["id"]] = $sumonnerSpell["key"];
        }

        $runesPages = $sum->getRunePages($summonerId, $safeRegion);

        /* LIVE GAME */

        $lg_data = array();
        /*if(isset($currentGame['participants']))
        {
            //var_dump($currentGame['participants'] );exit();
            foreach($currentGame['participants'] as $participant)
            {
                $lg_soloq = $sum->getSummonerRank($participant['summonerId']);
                if(!isset($lg_soloq))
                {
                    $lg_soloqimg = "unranked_";
                }
                else
                {
                    $lg_soloqimg = strtolower($lg_soloq['tier']) . '_' . $lg_soloq['entries'][0]['division'];
                }
                $lg_data[$participant['summonerId']]['rank'] = $lg_soloq;
                $lg_data[$participant['summonerId']]['img'] = $lg_soloqimg;
            }
        }*/

        return $this->render('AppBundle:Summoner:index.html.twig',
            array(
                'championsMastery' => $championsMastery,
                'summoner' => $summoner,
                'soloq' => $soloq,
                'soloqimg' => $soloqimg,
                'static_data_version' => $static_data_version,
                'currentGame' => $currentGame,
                'summonerSpells' => $summonerSpells,
                'champions' => $temp,
                'rankedStats' => $rankedStats,
                'runePages' => $runesPages['data'],
                'runes' => $runesPages['images'],
                'live_game_data' => $lg_data
            ));
    }
    
    public function indexAction2($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $topChampionsMastery = $api->getMasteryTopChampions($summonerId);
        $static_data_version = $this->container->getParameter('static_data_version');


        $currentGame = $api->getCurrentGame($summonerId);
        $sumonnerSpellsData = $api->getStaticSummonerSpells();
        $summonerSpells = array();
        foreach($sumonnerSpellsData["data"] as $sumonnerSpell)
        {
            $summonerSpells[$sumonnerSpell["id"]] = $sumonnerSpell["key"];
        }

        $soloq = $sum->getSummonerRank($summonerId);
        if(!isset($soloq))
        {
            $soloqimg = "unranked_";
        }
        else
        {
            $soloqimg = strtolower($soloq['tier']) . '_' . $soloq['entries'][0]['division'];
        }

        $champions = $em->getRepository('AppBundle:StaticData\Champion')->findAll();
        $temp = array();
        foreach($champions as $champion)
        {
            $temp[$champion->getId()] = array('key' => $champion->getKey());
        }
        for($i = 0; $i < count($topChampionsMastery); $i++)
        {
            $arr = array('championKey' => $temp[$topChampionsMastery[$i]['championId']]['key']);
            $topChampionsMastery[$i] = array_merge($topChampionsMastery[$i], $arr);
        }
        // Switch du 1er et 2eme
        $tempChampMastery = $topChampionsMastery[0];
        $topChampionsMastery[0] = $topChampionsMastery[1];
        $topChampionsMastery[1] = $tempChampMastery;

        $summoner =  $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerIdSafe($region, $summonerId);
        if(empty($summoner))
        {
            $summoner = $sum->createSummoner($region, $summonerId);
        }
        else
        {
            $summoner = $summoner[0];
        }

        // Chests
        $chests = $api->getChampionsMastery($summonerId);

        for($i = 0; $i < count($chests); $i++)
        {
            $temp[$chests[$i]['championId']] = array_merge($temp[$chests[$i]['championId']], $chests[$i]);
        }

        /* Ranked stats*/
        //TODO: il faut prévoir le cas où il n'y a pas de données renvoyées pour la saison en cours
        //$rankedStats = $api->getRankedStatsBySummonerId($summonerId, 6);
        $rankedStats = $sum->updateRankedStats($summonerId);

        return $this->render('AppBundle:Summoner:index.html.twig',
            array(
                'topChampionsMastery' => $topChampionsMastery,
                'summoner' => $summoner,
                'soloq' => $soloq,
                'soloqimg' => $soloqimg,
                'static_data_version' => $static_data_version,
                'currentGame' => $currentGame,
                'summonerSpells' => $summonerSpells,
                'champions' => $temp,
                'rankedStats' => $rankedStats['champions'],
                'averageRankedStats' => $rankedStats['average'],
            ));
    }

    public function chestsAction($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $chests = $api->getChampionsMastery($summonerId);
        $champions = $em->getRepository('AppBundle:StaticData\Champion')->findAll();
        $temp = array();
        foreach($champions as $champion)
        {
            $temp[$champion->getId()] = array('key' => $champion->getKey());
        }

        for($i = 0; $i < count($chests); $i++)
        {
            $temp[$chests[$i]['championId']] = array_merge($temp[$chests[$i]['championId']], $chests[$i]);
        }
        $champions = $temp;
        $summoner =  $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region, $summonerId);

        return $this->render('AppBundle:Summoner:chests.html.twig',
            array(
                'champions' => $champions,
                'summoner' => $summoner,
            ));
    }
}
