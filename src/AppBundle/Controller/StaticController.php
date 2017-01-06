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

    public function aboutAction()
	{
		return $this->render('AppBundle:Static:about.html.twig');
	}

    public function searchbarAction(Request $request)
    {
        $api = $this->container->get('app.lolapi');
        // On doit traiter le nom du summoner
        $summonerName =  $api->toSafeLowerCase($request->request->get('searchbar-summonerName', 'UTF-8'));

        $sum = $this->container->get('app.lolsummoner');
        $region = $sum->getRegionBySlug($request->request->get('searchbar-region'));
        $summoner = $api->getSummonerByNames($region, array($summonerName));
        if($api->getResponseCode() == 404)
        {
            throw new NotFoundHttpException('Summoner not existing');
        }
        return $this->redirectToRoute('app_summoner',
            array(
            'region' => $request->request->get('searchbar-region'),
            'summonerId' => $summoner[$summonerName]['id']
            )
        );
    }
}
