<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Summoner\Summoner;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SummonerController extends Controller
{
    public function ajaxCreateAction($region, $summonerId)
    {
        $sum = $this->container->get('app.lolsummoner');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        try
        {
            $region = $sum->getRegionBySlug($region);
            $sum->firstUpdateSummoner($region, $summonerId);
            $response->setContent(json_encode(array(
                'status' => 'OK',
            )));
        }
        catch(\Exception $e)
        {
            $response->setStatusCode(500);
            $response->setContent(json_encode(array(
                'error' => $e->getMessage(),
            )));
        }

        return $response;
    }

    public function indexAction($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $static_data_version = $this->container->getParameter('static_data_version');

        $region = $sum->getRegionBySlug($region);

        // On récupère le summoner en BDD
        $summoner = $em->getRepository('AppBundle:Summoner\Summoner')->findOneBy([
            'id' => $summonerId,
            'region' => $region
        ]);

        // Si le summoner n'existe pas encore en BDD, on le crée
        //TODO: Faire ça dans une méthode POST
        if (empty($summoner))
        {
            $summonerData = $api->getSummonerByIds($region, array($summonerId));
            if($api->getResponseCode() == 404)
            {
                //TODO: exception summoner not found
                throw new NotFoundHttpException('Summoner not existing');
            }
            $newSummoner = new Summoner($summonerId, $region);
            $newSummoner->setUser(null);
            $newSummoner->setName($summonerData[$summonerId]['name']);
            $newSummoner->setLevel($summonerData[$summonerId]['summonerLevel']);
            $newSummoner->setProfileIconId($summonerData[$summonerId]['profileIconId']);
            $date = date_create();
            date_timestamp_set($date, ($summonerData[$summonerId]['revisionDate']/1000));
            $newSummoner->setRevisionDate($date);
            $em->persist($newSummoner);
            $em->flush();
        }
        else
        {
            $newSummoner = $summoner;
        }
        if (empty($summoner) ||(!empty($summoner) && !$summoner->isFirstUpdated()))
        {
            return $this->render('AppBundle:Summoner:creating_summoner.html.twig',
                array(
                    'static_data_version' => $static_data_version,
                    'summoner' => $newSummoner
                ));
        }
        //return new Response("ok");
        return $this->indexAction3($region->getSlug(), $summonerId);
        return $this->render('AppBundle:Summoner:index.html.twig',
            array(
            ));
    }

    public function indexAction3($region, $summonerId)
    {
        $em = $this->get('doctrine')->getManager();
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $static_data_version = $this->container->getParameter('static_data_version');

        $safeRegion = $em->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => $region
        ]);
        if(empty($safeRegion))
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
        if (empty($summoner))
        {
            $summoner = $api->getSummonerByIds($safeRegion, array($summonerId));
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
        $rankedStats = $sum->getRankedStats($summoner);

        $soloq = $sum->getSummonerRank($safeRegion, $summonerId);
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
        $topChampionsMastery = $api->getMasteryTopChampions($safeRegion, $summonerId);
        if(!empty($topChampionsMastery))
        {
            for($i = 0; $i < count($topChampionsMastery); $i++)
            {
                $arr = array('championKey' => $temp[$topChampionsMastery[$i]['championId']]['key']);
                $topChampionsMastery[$i] = array_merge($topChampionsMastery[$i], $arr);
            }
            // Switch du 1er et 2eme
            $tempChampMastery = $topChampionsMastery[0];
            $topChampionsMastery[0] = $topChampionsMastery[1];
            $topChampionsMastery[1] = $tempChampMastery;
        }


        return $this->render('AppBundle:Summoner:index.html.twig',
            array(
                //'championsMastery' => $championsMastery,
                'topChampionsMastery' => $topChampionsMastery,
                'summoner' => $summoner,
                'soloq' => $soloq,
                'soloqimg' => $soloqimg,
                'static_data_version' => $static_data_version,
                //'currentGame' => $currentGame,
                //'summonerSpells' => $summonerSpells,
                'champions' => $temp,
                'rankedStats' => $rankedStats,
                //'live_game_data' => $lg_data
            ));
    }
}
