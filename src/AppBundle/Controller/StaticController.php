<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StaticController extends Controller
{
    public function homepageAction()
    {
        return $this->render('AppBundle:Static:homepage.html.twig');
    }

    public function notFoundAction()
    {
        throw new NotFoundHttpException("Page not found");
    }

    public function aboutAction()
    {
        return $this->render('AppBundle:Static:about.html.twig');
    }

    public function searchbarAction(Request $request)
    {
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        // On doit traiter le nom du summoner
        $originalName = $request->request->get('searchbar-summonerName', 'UTF-8');
        $summonerName = $api->toSafeLowerCase($originalName);

        $sum = $this->container->get('app.lolsummoner');
        $region = $sum->getRegionBySlug($request->request->get('searchbar-region'));
        $summoner = $api->getSummonerByNames($region, array($summonerName));
        if ($api->getResponseCode() == 404) {
            //$data = $sum->getSummonerByNameForAllRegions($summonerName);
            return $this->render('AppBundle:Summoner:not_existing.html.twig',
                array(
                    'region' => $request->request->get('searchbar-region'),
                    'name' => $originalName,
                    //'data' => $data,
                    'region' => $region,
                    'formattedName' => $summonerName
                )
            );
        }
        return $this->redirectToRoute('app_summoner',
            array(
                'region' => $request->request->get('searchbar-region'),
                'summonerId' => $summoner[$summonerName]['id']
            )
        );
    }
}
