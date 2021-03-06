<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Summoner\Summoner;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SummonerController extends Controller
{

    public function searchAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $sum = $this->container->get('app.lolsummoner');

        $user = $user = $this->getUser();
        // Si l'utilisateur n'est pas connecté
        if (empty($user)) {
            return $this->render('AppBundle:Lookup:search_error_not_logged_in.html.twig');
        } // Si l'utilisateur n'a pas de summoner enregistré
        else if (count($user->getSummoners()) == 0) {
            return $this->render('AppBundle:Lookup:search_error_no_summoner_registered.html.twig');
        } // Cas normal
        else {
            $region = $sum->getRegionBySlug('euw');
            $minElo = 1;
            $maxElo = 25;
            $sumonnerId = 29233320;
            $search = $em->getRepository('AppBundle:Summoner\Summoner')->findAllSummonersByRegionAndMinEloAndMaxElo($region, $sumonnerId, $minElo, $maxElo);

            return $this->render('AppBundle:Lookup:search.html.twig',
                array(
                    'summoners' => $user->getSummoners(),
                    'search' => $search
                ));
        }
    }

    public function ajaxCreateAction($region, $summonerId)
    {
        $this->container->get('logger')->debug('SummonerId = ' . $summonerId);
        $sum = $this->container->get('app.lolsummoner');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        try {
            $region = $sum->getRegionBySlug($region);
            $sum->firstUpdateSummoner($region, $summonerId);
            $response->setContent(json_encode(array(
                'status' => 'OK',
            )));
        } catch (\Exception $e) {
            $response->setStatusCode(500);
            $response->setContent(json_encode(array(
                'error' => $e->getMessage(),
            )));
        }

        return $response;
    }

    public function indexAction(Request $request, $region, $summonerId)
    {
        $this->container->get('logger')->debug('SummonerId = ' . $summonerId);
        $em = $this->get('doctrine')->getManager();
        /**  @var \AppBundle\Services\LoLAPI\LoLAPIService $api */
        $api = $this->container->get('app.lolapi');

        /**  @var \AppBundle\Services\SummonerService $sum */
        $sum = $this->container->get('app.lolsummoner');

        $region = $sum->getRegionBySlug($region);

        // On récupère le summoner en BDD
        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'summonerId' => $summonerId,
            'region' => $region
        ]);

        // Si le summoner n'existe pas encore en BDD, on le crée
        //TODO: Faire ça dans une méthode POST
        if (empty($summoner)) {
            $summonerData = $api->getSummonerBySummonerId($region, $summonerId);
            if ($api->getResponseCode() == 404) {
                //TODO: exception summoner not found
                throw new NotFoundHttpException('Summoner not existing');
            }
            $newSummoner = new Summoner($summonerId, $region);
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
        } else {
            $newSummoner = $summoner;
        }
        if (empty($summoner) || (!empty($summoner) && !$summoner->isFirstUpdated())) {
            return $this->render('AppBundle:Summoner:creating_summoner.html.twig',
                array(
                    'summoner' => $newSummoner
                ));
        }
        return $this->indexAction3($request, $region->getSlug(), $summonerId);
    }

    public function indexAction3(Request $request, $region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        /**  @var \AppBundle\Services\LoLAPI\LoLAPIService $api */
        $api = $this->container->get('app.lolapi');

        /**  @var \AppBundle\Services\SummonerService $sum */
        $sum = $this->container->get('app.lolsummoner');

        $safeRegion = $em->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => $region
        ]);
        if (empty($safeRegion)) {
            //TODO: lancer exception
            throw new NotFoundHttpException('Region not existing');
        }

        // On récupère le summoner en BDD
        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'summonerId' => $summonerId,
            'region' => $safeRegion
        ]);

        // Si le summoner n'existe pas encore en BDD, on le crée
        if (empty($summoner)) {
            $summoner = $api->getSummonerByIds($safeRegion, array($summonerId));
            if ($api->getResponseCode() == 404) {
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
            date_timestamp_set($date, ($summoner[$summonerId]['revisionDate'] / 1000));
            $newSummoner->setRevisionDate($date);

            $em->persist($newSummoner);
            $em->flush();
            $summoner = $newSummoner;
        }
        $rankedStats = $sum->getRankedStats($summoner);

        $ranks = $sum->getSummonerRank($safeRegion, $summonerId);
        if (!isset($soloq)) {
            $soloqimg = "unranked_";
        } else {
            $soloqimg = strtolower($soloq['tier']) . '_' . $soloq['entries'][0]['division'];
        }

        $language = $sum->getLanguageByRequestLocale($request);
        $champions = $sum->getChampionsSortedByIds($language);

        //$championsMastery = $sum->updateChampionsMastery($summonerId, $safeRegion);

        /*
        $currentGame = $api->getCurrentGame($summonerId);

        $sumonnerSpellsData = $api->getStaticSummonerSpells();
        $summonerSpells = array();
        foreach($sumonnerSpellsData["data"] as $sumonnerSpell)
        {
            $summonerSpells[$sumonnerSpell["id"]] = $sumonnerSpell["key"];
        }
*/
        /* LIVE GAME */

        //$lg_data = array();
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
        $topChampionsMasteryData = $api->getChampionsMastery($safeRegion, $summonerId);
        $topChampionsMastery = array();
        // Pour tester avec moins de 3 champion mastery
        //$topChampionsMasteryData = array_slice($topChampionsMasteryData, 0, 3);
        for ($i = 0; $i < count($topChampionsMasteryData) && $i < 3; $i++) {
            $arr = array('championKey' => $champions[$topChampionsMasteryData[$i]['championId']]['key']);
            $topChampionsMastery[$i] = array_merge($topChampionsMasteryData[$i], $arr);
        }

        return $this->render('AppBundle:Summoner:index.html.twig',
            array(
                //'championsMastery' => $championsMastery,
                'topChampionsMastery' => $topChampionsMastery,
                'summoner' => $summoner,
                'ranks' => $ranks,
                'soloqimg' => $soloqimg,
                //'currentGame' => $currentGame,
                //'summonerSpells' => $summonerSpells,
                'champions' => $champions,
                'rankedStats' => $rankedStats,
                //'live_game_data' => $lg_data
            ));
    }
}
