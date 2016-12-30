<?php


namespace AppBundle\Controller;

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
            //var_dump($champions[103]);
            //exit();

            $summoner =  $em->getRepository('AppBundle:Summoner\Summoner')->findOneByRegionAndSummonerId($region, $summonerId);
            $template =  $this->render('AppBundle:Summoner:_chests.html.twig',
                array(
                    'champions' => $champions,
                    'summoner' => $summoner,
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
