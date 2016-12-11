<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\LoLAPI\LoLAPIService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        $summonerName =  str_replace(' ', '', strtolower($request->request->get('searchbar-summonerName')));
        $summoner = $api->getSummonerByNames(array($summonerName));
        if(isset($summoner['errorCode']))
        {
            throw new NotFoundHttpException('Sorry not existing!');
        }
        return $this->redirectToRoute('app_summoner', array('region' => $request->request->get('searchbar-region'),
        'summonerId' => $summoner[$summonerName]['id']));
    }
}
