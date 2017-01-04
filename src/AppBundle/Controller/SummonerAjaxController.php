<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Summoner\Summoner;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class SummonerAjaxController extends Controller
{
    public function linkSummonerToUserBlankAction(Request $request)
    {
        $template = $this->render('AppBundle:Account:_link-account-blank.html.twig')->getContent();
        return new Response($template);
    }

    public function linkSummonerToUserAction(Request $request, $summonerName)
    {
        $authenticatedUser = $this->get('security.token_storage')->getToken()->getUser();
        if(!$request->isXmlHttpRequest())
        {
            return new JsonResponse(array('httpCode' => 400, 'error' => 'Requête non AJAX'));
        }
        elseif (!$authenticatedUser)
        {
            return new JsonResponse(array('httpCode' => 401, 'error' => 'Authentification nécessaire'));
        }
        else
        {
            $summonerService = $this->container->get('app.lolsummoner');

            $linkMessage = $summonerService->linkSummonerToUser($authenticatedUser, $summonerName);
            $template = $this->render('AppBundle:Account:_link-account.html.twig',
                array(
                    'linkMessage' => $linkMessage,
                ))
                ->getContent();
            return new Response($template);
        }
    }

    public function chestsAction(Request $request, $summonerId, $region)
    {
        $static_data_version = $this->container->getParameter('static_data_version');
        if(!$request->isXmlHttpRequest())
        {
            return new JsonResponse(array('httpCode' => 400, 'error' => 'Requête non AJAX'));
        }
        else
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
            $owned = 0;
            $remaining = 0;
            foreach($champions as $key => $champion)
            {
                if(isset($champion['chestGranted']) && ($champion['chestGranted']))
                {
                    $owned = $owned + 1;
                    $dataCategory[$champion['key']] = 1;
                }
                else
                {
                    $remaining = $remaining + 1;
                    $dataCategory[$champion['key']] = 2;
                }
            }

            $summoner =  $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region, $summonerId);
            $template =  $this->render('AppBundle:Summoner:_chests.html.twig',
                array(
                    'champions' => $champions,
                    'summoner' => $summoner,
                    'owned' => $owned,
                    'remaining' => $remaining,
                    'dataCategory' => $dataCategory,
                    'static_data_version' => $static_data_version
                ))
            ->getContent();
            return new Response($template);
        }
    }

    public function championMasteriesAction(Request $request, $summonerId, $region)
    {
        $static_data_version = $this->container->getParameter('static_data_version');
        if(!$request->isXmlHttpRequest())
        {
            return new JsonResponse(array('httpCode' => 400, 'error' => 'Requête non AJAX'));
        }
        else
        {
            $em = $this->get('doctrine')->getManager();
            $api = $this->container->get('app.lolapi');
            $championsMasteryData = $api->getChampionsMastery($summonerId);
            //$chests = $api->getChampionsMastery($summonerId);
            $champions = $em->getRepository('AppBundle:StaticData\Champion')->findAll();
            $temp = array();
            foreach($champions as $champion)
            {
                $temp[$champion->getId()] = array('key' => $champion->getKey());
            }

            for($i = 0; $i < count($championsMasteryData); $i++)
            {
                $temp[$championsMasteryData[$i]['championId']] = array_merge($temp[$championsMasteryData[$i]['championId']], $championsMasteryData[$i]);
            }
            $champions = $temp;
            //var_dump($champions[103]);
            //exit();

            $summoner =  $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region, $summonerId);
            $template =  $this->render('AppBundle:Summoner:_remaining_champion_mastery.html.twig',
                array(
                    'championsMastery' => $championsMasteryData,
                    'champions' => $champions,
                    'summoner' => $summoner,
                    'static_data_version' => $static_data_version
                ))
                ->getContent();
            return new Response($template);
        }
    }

    public function runesAction(Request $request, $summonerId, $region)
    {
        $static_data_version = $this->container->getParameter('static_data_version');
        if(!$request->isXmlHttpRequest())
        {
            return new JsonResponse(array('httpCode' => 400, 'error' => 'Requête non AJAX'));
        }
        else
        {
            $sum = $this->container->get('app.lolsummoner');
            $em = $this->get('doctrine')->getManager();

            $region = $sum->getRegionBySlug($region);
            // On récupère le summoner en BDD
            $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
                'id' => $summonerId,
                'region' => $region
            ]);

            $runesPages = $sum->getRunePages($summoner);

            $template =  $this->render('AppBundle:Summoner:_runes.html.twig',
                array(
                    'runePages' => $runesPages['data'],
                    'runes' => $runesPages['images'],
                    'static_data_version' => $static_data_version
                ))
                ->getContent();
            return new Response($template);
        }
    }

    public function liveGameAction(Request $request, $summonerId, $region)
    {
        $static_data_version = $this->container->getParameter('static_data_version');
        if(!$request->isXmlHttpRequest())
        {
            return new JsonResponse(array('httpCode' => 400, 'error' => 'Requête non AJAX'));
        }
        else
        {
            $api = $this->container->get('app.lolapi');
            $sum = $this->container->get('app.lolsummoner');
            $em = $this->get('doctrine')->getManager();

            $region = $sum->getRegionBySlug($region);
            // On récupère le summoner en BDD
            $mainSummoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
                'id' => $summonerId,
                'region' => $region
            ]);

            $liveGame = $sum->getLiveGame($mainSummoner);
            $champions = $sum->getChampionsSortedByIds();
            $runeData = null;
            $playerStats = array();
            if(isset($liveGame['currentGame']['participants']))
            {
                $runeData = $sum->getRunePageByData($liveGame['currentGame']['participants']);
                foreach($liveGame['currentGame']['participants'] as $player)
                {
                    // On récupère le summoner en BDD
                    $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
                        'id' => $player['summonerId'],
                        'region' => $region
                    ]);

                    // Si le summoner n'existe pas encore en BDD, on le crée
                    if (empty($summoner))
                    {
                        $summonerData = $api->getSummonerByIds(array($player['summonerId']));
                        if($api->getResponseCode() == 404)
                        {
                            //TODO: exception summoner not found
                            throw new NotFoundHttpException('Summoner not existing');
                        }
                        $newSummoner = new Summoner($player['summonerId'], $region);
                        $newSummoner->setUser(null);
                        $newSummoner->setName($summonerData[$player['summonerId']]['name']);
                        $newSummoner->setLevel($summonerData[$player['summonerId']]['summonerLevel']);
                        $newSummoner->setProfileIconId($summonerData[$player['summonerId']]['profileIconId']);
                        $date = date_create();
                        date_timestamp_set($date, ($summonerData[$player['summonerId']]['revisionDate']/1000));
                        $newSummoner->setRevisionDate($date);
                        $em->persist($newSummoner);
                        $em->flush();
                        $sum->firstUpdateSummoner($region, $player['summonerId']);
                    }

                    $rankedStats = $em->getRepository('AppBundle:Summoner\RankedStats')->findOneBy([
                        'summonerId' => $player['summonerId'],
                        'regionId' => $region->getId(),
                        'season' => 7,
                        'championId' => 0
                    ]);
                    $rankedStats2 = $em->getRepository('AppBundle:Summoner\RankedStats')->findOneBy([
                        'summonerId' => $player['summonerId'],
                        'regionId' => $region->getId(),
                        'season' => 7,
                        'championId' => $player['championId']
                    ]);
                    $playerStats[$player['summonerId']]['general'] = $rankedStats;
                    $playerStats[$player['summonerId']]['champion'] = $rankedStats2;
                }
            }

            $template =  $this->render('AppBundle:Summoner:_live_game.html.twig',
                array(
                    'currentGame' => $liveGame['currentGame'],
                    'summonerSpells' => $liveGame['summonerSpells'],
                    'live_game_data' => $liveGame['live_game'],
                    'summoner' => $mainSummoner,
                    'champions' => $champions,
                    'runesImg' => $runeData['images'],
                    'runesStats' => $runeData['stats'],
                    'playerStats' => $playerStats,
                    'static_data_version' => $static_data_version
                ))
                ->getContent();
            return new Response($template);
        }
    }
    
    public function updateInfosAction()
    {
        return new Response();
    }
}
