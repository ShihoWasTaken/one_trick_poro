<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Summoner\Summoner;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SummonerAjaxController extends Controller
{
    public function linkSummonerToUserBlankAction(Request $request)
    {
        $template = $this->render('AppBundle:Account:_link-account-blank.html.twig')->getContent();
        return new Response($template);
    }

    public function linkSummonerToUserAction(Request $request)
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
            $summonerName = $request->request->get('summonerName');
            $region = $request->request->get('region');
            $summonerService = $this->container->get('app.lolsummoner');

            $linkMessage = $summonerService->linkSummonerToUser($authenticatedUser, $summonerName, $region);
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
            $sum = $this->container->get('app.lolsummoner');
            $region = $sum->getRegionBySlug($region);
            $language = $sum->getLanguageByRequestLocale($request);
            $chests = $api->getChampionsMastery($region, $summonerId);
            $champions = $sum->getChampionsSortedByIds($language);

            for($i = 0; $i < count($chests); $i++)
            {
                $champions[$chests[$i]['championId']] = array_merge($champions[$chests[$i]['championId']], $chests[$i]);
            }
            $owned = 0;
            $remaining = 0;
            $dataCategory = array();
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

            $summoner =  $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region->getSlug(), $summonerId);
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
            $sum = $this->container->get('app.lolsummoner');
            $region = $sum->getRegionBySlug($region);
            $championsMasteryData = $api->getChampionsMastery($region, $summonerId);
            //$chests = $api->getChampionsMastery($summonerId);
            $language = $sum->getLanguageByRequestLocale($request);
            $champions = $sum->getChampionsSortedByIds($language);
            for($i = 0; $i < count($championsMasteryData); $i++)
            {
                $champions[$championsMasteryData[$i]['championId']] = array_merge($champions[$championsMasteryData[$i]['championId']], $championsMasteryData[$i]);
            }
            //var_dump($champions[103]);
            //exit();

            $summoner =  $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region->getSlug(), $summonerId);
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
            //$runeData = $sum->getRunePagesInfo($region, $runesPages['data']);

            $language = $sum->getLanguageByRequestLocale($request);

            // TODO: rechercher seulement les runes concernées
            $runesTranslations = $em->getRepository('AppBundle:StaticData\Translation\RuneTranslation')->findBy([
                'languageId' => $language->getId()
            ]);
            $translations = array();
            foreach($runesTranslations as $translation)
            {
                $translations[$translation->getRuneId()] = $translation;
            }

            $template =  $this->render('AppBundle:Summoner:_runes.html.twig',
                array(
                    'runePages' => $runesPages['data'],
                    'runes' => $runesPages['images'],
                    'translations' => $translations,
                    //'runesData' => $runeData,
                    'static_data_version' => $static_data_version
                ))
                ->getContent();
            return new Response($template);
        }
    }

    public function masteriesAction(Request $request, $summonerId, $region)
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

            $masteriesPages = $sum->getMasteriesPages($summoner);

            $masteries = $em->getRepository('AppBundle:StaticData\Mastery')->findAll();
            $language = $sum->getLanguageByRequestLocale($request);

            $masteriesTranslations = $em->getRepository('AppBundle:StaticData\Translation\MasteryTranslation')->findBy([
                'languageId' => $language->getId()
            ]);
            $translations = array();
            foreach($masteriesTranslations as $translation)
            {
                $translations[$translation->getMasteryId()] = $translation;
            }

            $template =  $this->render('AppBundle:Summoner:_masteries.html.twig',
                array(
                    'masteriesPages' => $masteriesPages,
                    'masteries' => $masteries,
                    'translations' => $translations,
                    //'masteriesPages' => $masteriesPages['data'],
                    //'$masteries' => $masteriesPages['images'],
                    'static_data_version' => $static_data_version
                ))
                ->getContent();
            return new Response($template);
        }
    }

    public function historyAction(Request $request, $summonerId, $region)
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

            $history = $sum->getMatchHistory($summoner);
            $language = $sum->getLanguageByRequestLocale($request);
            $champions = $sum->getChampionsSortedByIds($language);
            $summonerSpells = $sum->getSummonerSpellsSortedById($summoner->getRegion());
            $gamesItems = array();
            $gamesPlayers = array();
            $playersIds = array();
            foreach($history['games'] as $game)
            {
                for($i = 0; $i <= 6; $i++)
                {
                    if(isset($game['stats']['item' . $i]))
                    {
                        $gamesItems[$game['gameId']][$i] = $game['stats']['item' . $i];
                    }
                    else
                    {
                        $gamesItems[$game['gameId']][$i] = null;
                    }
                }
                $playersIds[] = $summonerId;
                foreach($game['fellowPlayers'] as $player)
                {
                    $playersIds[] = $player['summonerId'];
                }
                $summonerNames = $sum->getSummonerNamesByIds($summoner->getRegion(), $playersIds);
                foreach($game['fellowPlayers'] as $player)
                {
                    $gamesPlayers[$game['gameId']][$player['teamId']][] = $player;
                }
                $gamesPlayers[$game['gameId']][$game['teamId']][] = array(
                    'summonerId' => $history['summonerId'],
                    'championId' => $game['championId']
                );
            }

            $template =  $this->render('AppBundle:Summoner:_history.html.twig',
                array(
                    'history' => $history,
                    'champions' => $champions,
                    'summonerSpells' => $summonerSpells,
                    'summonerNames' => $summonerNames,
                    'gamesItems' => $gamesItems,
                    'gamesPlayers' => $gamesPlayers,
                    'summoner' => $summoner,
                    'static_data_version' => $static_data_version
                ))
                ->getContent();
            return new Response($template);
        }
    }

    public function liveGameAction(Request $request, $summonerId, $region)
    {
        //TODO: afficher les infos progressivement si un summoner n'est pas créé
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

            if(isset($liveGame['currentGame']['status']['status_code']))
            {
                $template =  $this->render('AppBundle:Summoner:_live_game_not_found.html.twig',
                    array(
                ))
                    ->getContent();
                return new Response($template);
            }

            $language = $sum->getLanguageByRequestLocale($request);
            $champions = $sum->getChampionsSortedByIds($language);
            $runeData = null;
            $playerStats = array();
            if(isset($liveGame['currentGame']['participants']))
            {
                $runeData = $sum->getRunePageByData($region, $liveGame['currentGame']['participants']);
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
                        $summonerData = $api->getSummonerByIds($region,array($player['summonerId']));
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
            $masteries = $em->getRepository('AppBundle:StaticData\Mastery')->findAll();
            $language = $sum->getLanguageByRequestLocale($request);

            $masteriesTranslations = $em->getRepository('AppBundle:StaticData\Translation\MasteryTranslation')->findBy([
                'languageId' => $language->getId()
            ]);
            $translations = array();
            foreach($masteriesTranslations as $translation)
            {
                $translations[$translation->getMasteryId()] = $translation;
            }
            $masteriesPages = array();
            foreach($liveGame['currentGame']['participants'] as $player)
            {
                foreach($player['masteries'] as $mastery)
                {
                    $masteriesPages[$player['summonerId']][$mastery['masteryId']] = $mastery['rank'];
                }
                if($player['teamId'] == 100)
                {
                    $players['blue'][] = $player;
                }
                else
                {
                    $players['purple'][] = $player;
                }
            }
            $bannedChampions['blue'] = array();
            $bannedChampions['purple'] = array();
            foreach($liveGame['currentGame']['bannedChampions'] as $champion)
            {
                if($champion['teamId'] == 100)
                {
                    $bannedChampions['blue'][] = $champion;
                }
                else
                {
                    $bannedChampions['purple'][] = $champion;
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
                    'masteriesPages' => $masteriesPages,
                    'masteries' => $masteries,
                    'translations' => $translations,
                    'players' => $players,
                    'bannedChampions' => $bannedChampions,
                    'static_data_version' => $static_data_version
                ))
                ->getContent();
            return new Response($template);
        }
    }
}
