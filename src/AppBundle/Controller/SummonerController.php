<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Repository\Summoner;
use AppBundle\Repository\Summoner\SummonerRepository;

class SummonerController extends Controller
{
   /* public function indexAction($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();

        // On récupère le summoner en BDD
        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'id' => $summonerId
        ]);

        // Si le summoner n'existe pas encore en BDD, on le crée
        if ($summoner == null)
        {
            $championRankedStats = new rankedStats($summonerId, $season, $championData['id']);
        }
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
                'rankedStats' => $rankedStats,
            ));
    }*/
    
    public function indexAction($region, $summonerId)
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
                'rankedStats' => $rankedStats,
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
