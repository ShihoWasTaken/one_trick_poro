<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ChampionMasteryController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle:ChampionMastery:index.html.twig',
            array(
                //'topChampionsMastery' => $topChampionsMastery,
                //'summoner' => $summoner,
            ));
    }

    public function summonerAction($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $topChampionsMastery = $api->getMasteryTopChampions($summonerId);

        $summoner =  $em->getRepository('AppBundle:Summoner')->findOneByRegionAndSummonerId($region, $summonerId);
        return $this->render('AppBundle:ChampionMastery:index.html.twig',
            array(
                'topChampionsMastery' => $topChampionsMastery,
                'summoner' => $summoner,
            ));
    }

}
