<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DevController extends Controller
{
    public function featuredAction()
    {
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $region = $sum->getRegionBySlug('euw');
        $gamesData = $api->getFeaturedGames($region);
        return $this->render('AppBundle:Dev:featured.html.twig',
            array(
                'games' => $gamesData['gameList']
            ));
    }
}
