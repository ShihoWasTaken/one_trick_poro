<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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

    public function monitoringAction()
    {
        $data = shell_exec('cat /proc/meminfo');
        return $this->render('AppBundle:Dev:monitoring.html.twig',
            array(
                'data' => $data
            ));
    }

    public function monitoringAjaxAction()
    {

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $data = shell_exec('cat /proc/meminfo');
        $lines = explode(PHP_EOL, $data);
        $return = $lines[0] . '<br>' . $lines[1] . '<br>' . $lines[2];
        $response->setContent(json_encode(array(
            'data' => $return,
        )));

        return $response;
    }
}
