<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SummonerController extends Controller
{
    public function indexAction($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $topChampionsMastery = $api->getMasteryTopChampions($summonerId);
        $static_data_version = $this->container->getParameter('static_data_version');

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

        $summoner =  $em->getRepository('AppBundle:Summoner')->findOneByRegionAndSummonerIdSafe($region, $summonerId);
        if(empty($summoner))
        {
            $summoner = $sum->createSummoner($region, $summonerId);
        }
        else
        {
            $summoner = $summoner[0];
        }
        return $this->render('AppBundle:Summoner:index.html.twig',
            array(
                'topChampionsMastery' => $topChampionsMastery,
                'summoner' => $summoner,
                'soloq' => $soloq,
                'soloqimg' => $soloqimg,
                'static_data_version' => $static_data_version,
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
        $summoner =  $em->getRepository('AppBundle:Summoner')->findOneByRegionAndSummonerId($region, $summonerId);

        return $this->render('AppBundle:Summoner:chests.html.twig',
            array(
                'champions' => $champions,
                'summoner' => $summoner,
            ));
    }
}
