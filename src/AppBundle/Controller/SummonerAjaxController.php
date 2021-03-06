<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Summoner\Summoner;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SummonerAjaxController extends AbstractAjaxController
{
    public function updateSummonerAction(Request $request, $summonerId, $region)
    {
        $translator = $this->get('translator');
        $response = new JsonResponse();

        $region = $this->get('app.lolsummoner')->getRegionBySlug($region);
        $em = $this->get('doctrine')->getManager();
        $databaseSummoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'summonerId' => $summonerId,
            'region' => $region
        ]);

        if ($databaseSummoner->isUpdatable()) {
            $this->get('app.lolsummoner')->extraSummonerUpdate($databaseSummoner);
            $response->setData(array(
                'message' => 'success'
            ));
        } else {
            $response->setData(array(
                'errorMessage' => $translator->trans('summoner.update.waiting.message', array('%time%' => $databaseSummoner->secondsBeforeNextUpdate()))
            ));
        }

        return $response;
    }

    public function linkSummonerToUserBlankAction(Request $request)
    {
        $template = $this->render('AppBundle:Account:_link-account-blank.html.twig')->getContent();
        return new Response($template);
    }

    public function linkSummonerToUserAction(Request $request)
    {
        $authenticatedUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$authenticatedUser) {
            return new JsonResponse(array('httpCode' => 401, 'error' => 'Authentification nécessaire'));
        } else {
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

    public function unlinkSummonerToUserAction(Request $request)
    {
        $authenticatedUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$authenticatedUser) {
            return new JsonResponse(array('httpCode' => 401, 'error' => 'Authentification nécessaire'));
        } else {
            $summonerName = $request->request->get('summonerName');
            $region = $request->request->get('region');
            $summonerService = $this->container->get('app.lolsummoner');

            $linkMessage = $summonerService->unlinkSummonerToUser($authenticatedUser, $summonerName, $region);
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
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $region = $sum->getRegionBySlug($region);
        $language = $sum->getLanguageByRequestLocale($request);
        $chests = $api->getChampionsMastery($region, $summonerId);
        $champions = $sum->getChampionsSortedByIds($language);

        for ($i = 0; $i < count($chests); $i++) {
            $champions[$chests[$i]['championId']] = array_merge($champions[$chests[$i]['championId']], $chests[$i]);
        }
        $owned = 0;
        $remaining = 0;
        $dataCategory = array();
        foreach ($champions as $key => $champion) {
            if (isset($champion['chestGranted']) && ($champion['chestGranted'])) {
                $owned = $owned + 1;
                $dataCategory[$champion['key']] = 1;
            } else {
                $remaining = $remaining + 1;
                $dataCategory[$champion['key']] = 2;
            }
        }

        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region->getSlug(), $summonerId);
        $template = $this->render('AppBundle:Summoner:_chests.html.twig',
            array(
                'champions' => $champions,
                'summoner' => $summoner,
                'owned' => $owned,
                'remaining' => $remaining,
                'dataCategory' => $dataCategory
            ))
            ->getContent();
        return new Response($template);
    }

    public function championMasteriesAction(Request $request, $summonerId, $region)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $region = $sum->getRegionBySlug($region);
        $championsMasteryData = $api->getChampionsMastery($region, $summonerId);
        //$chests = $api->getChampionsMastery($summonerId);
        $language = $sum->getLanguageByRequestLocale($request);
        $champions = $sum->getChampionsSortedByIds($language);
        for ($i = 0; $i < count($championsMasteryData); $i++) {
            $champions[$championsMasteryData[$i]['championId']] = array_merge($champions[$championsMasteryData[$i]['championId']], $championsMasteryData[$i]);
        }
        //var_dump($champions[103]);
        //exit();

        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region->getSlug(), $summonerId);
        $template = $this->render('AppBundle:Summoner:_remaining_champion_mastery.html.twig',
            array(
                'championsMastery' => $championsMasteryData,
                'champions' => $champions,
                'summoner' => $summoner
            ))
            ->getContent();
        return new Response($template);
    }

    public function historyAction(Request $request, $summonerId, $region)
    {
        /** @var \AppBundle\Services\SummonerService $api */
        $sum = $this->container->get('app.lolsummoner');
        $em = $this->get('doctrine')->getManager();

        $region = $sum->getRegionBySlug($region);
        // On récupère le summoner en BDD
        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy(['summonerId' => $summonerId,
            'region' => $region]);

        $history = $sum->getMatchHistory($summoner);
        $language = $sum->getLanguageByRequestLocale($request);
        $champions = $sum->getChampionsSortedByIds($language);
        $summonerSpells = $sum->getSummonerSpellsSortedById($summoner->getRegion());
        $gamesItems = array();
        $gamesPlayers = array();
        $playersIds = array();
        foreach ($history['matches'] as $game) {
            for ($i = 0;
                 $i <= 6;
                 $i++) {
                if (isset($game['stats']['item' . $i])) {
                    $gamesItems[$game['gameId']][$i] = $game['stats']['item' . $i];
                } else {
                    $gamesItems[$game['gameId']][$i] = null;
                }
            }
            $playersIds[] = $summonerId;
            foreach ($game['fellowPlayers'] as $player) {
                $playersIds[] = $player['summonerId'];
            }
            foreach ($game['fellowPlayers'] as $player) {
                $gamesPlayers[$game['gameId']][$player['teamId']][] = $player;
            }
            $gamesPlayers[$game['gameId']][$game['teamId']][] = array(
                'summonerId' => $history['summonerId'],
                'championId' => $game['championId']
            );
        }

        $summonerNames = $sum->getSummonerNamesByIds($summoner->getRegion(), $playersIds);

        $template = $this->render('AppBundle:Summoner:_history.html.twig',
            array(
                'history' => $history,
                'champions' => $champions,
                'summonerSpells' => $summonerSpells,
                'summonerNames' => $summonerNames,
                'gamesItems' => $gamesItems,
                'gamesPlayers' => $gamesPlayers,
                'summoner' => $summoner
            ))
            ->getContent();
        return new Response($template);
    }

    public function liveGameAction(Request $request, $summonerId, $region)
    {
        //TODO: afficher les infos progressivement si un summoner n'est pas créé
        /** @var \AppBundle\Services\LoLAPI\LoLAPIService $api */
        $api = $this->container->get('app.lolapi');

        /** @var \AppBundle\Services\SummonerService $sum */
        $sum = $this->container->get('app.lolsummoner');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine')->getManager();

        $region = $sum->getRegionBySlug($region);
        // On récupère le summoner en BDD
        $mainSummoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'summonerId' => $summonerId,
            'region' => $region
        ]);

        $liveGame = $sum->getLiveGame($mainSummoner);

        if (isset($liveGame['currentGame']['status']['status_code'])) {
            $template = $this->render('AppBundle:Summoner:_live_game_not_found.html.twig',
                array())
                ->getContent();
            return new Response($template);
        }

        $language = $sum->getLanguageByRequestLocale($request);
        $champions = $sum->getChampionsSortedByIds($language);
        $playerStats = array();
        if (isset($liveGame['currentGame']['participants'])) {
            foreach ($liveGame['currentGame']['participants'] as $player) {
                // On récupère le summoner en BDD
                $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
                    'summonerId' => $player['summonerId'],
                    'region' => $region
                ]);

                // Si le summoner n'existe pas encore en BDD, on le crée
                if (empty($summoner)) {
                    $summonerData = $api->getSummonerBySummonerId($region, $player['summonerId']);
                    if ($api->getResponseCode() == 404) {
                        //TODO: exception summoner not found
                        throw new NotFoundHttpException('Summoner not existing');
                    }
                    $newSummoner = new Summoner($player['summonerId'], $region);
                    $newSummoner->setUser(null);
                    $newSummoner->setAccountId($summonerData['accountId']);
                    $newSummoner->setName($summonerData['name']);
                    $newSummoner->setLevel($summonerData['summonerLevel']);
                    $newSummoner->setProfileIconId($summonerData['profileIconId']);
                    $date = date_create();
                    date_timestamp_set($date, ($summonerData['revisionDate'] / 1000));
                    $newSummoner->setRevisionDate($date);
                    $em->persist($newSummoner);
                    $em->flush();
                    $sum->firstUpdateSummoner($region, $player['summonerId']);

                    // Mis à jour des rank dans les variables
                    $lg_soloq = $sum->getSummonerRank($region, $player['summonerId']);

                    if (!isset($lg_soloq['solo'])) {
                        $lg_soloqimg = "unranked_";
                        $liveGame['live_game']['rank'] = 'Unranked';
                    } else {
                        $lg_soloq = $lg_soloq['solo'];
                        $lg_soloqimg = $lg_soloq->getTier()->getImage();
                        $liveGame['live_game']['rank'] = $lg_soloq->getTier()->getName();
                    }
                    $liveGame['live_game']['img'] = $lg_soloqimg;
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

        foreach ($liveGame['currentGame']['participants'] as $player) {
            if ($player['teamId'] == 100) {
                $players['blue'][] = $player;
            } else {
                $players['purple'][] = $player;
            }
        }
        $bannedChampions['blue'] = array();
        $bannedChampions['purple'] = array();
        foreach ($liveGame['currentGame']['bannedChampions'] as $champion) {
            if ($champion['teamId'] == 100) {
                $bannedChampions['blue'][] = $champion;
            } else {
                $bannedChampions['purple'][] = $champion;
            }
        }

        $template = $this->render('AppBundle:Summoner:_live_game.html.twig',
            array(
                'currentGame' => $liveGame['currentGame'],
                'summonerSpells' => $liveGame['summonerSpells'],
                'live_game_data' => $liveGame['live_game'],
                'summoner' => $mainSummoner,
                'champions' => $champions,
                'playerStats' => $playerStats,
                'players' => $players,
                'bannedChampions' => $bannedChampions
            ))
            ->getContent();
        return new Response($template);
    }
}
