<?php

namespace AppBundle\Controller;

use AppBundle\Services\MonitoringService;
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
        /**
         * @var \AppBundle\Services\MonitoringService $monitoringService
         */
        $monitoringService = $this->container->get('app.monitoring');
        $data = shell_exec('cat /proc/meminfo');
        return $this->render('AppBundle:Dev:monitoring.html.twig',
            array(
                'data' => $data,
                'ip' => $monitoringService->getIpAdress(),
                'country' => $monitoringService->ip_info("46.182.41.35",'country')
            ));
    }

    public function monitoringAjaxAction()
    {

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        /**
         * @var \AppBundle\Services\MonitoringService $monitoringService
         */
        $monitoringService = $this->container->get('app.monitoring');
        $ramInfos = $monitoringService->getRamInfos();

        $cpuInfos = $monitoringService->getCPULoad();

        $diskSpace = $monitoringService->getTotalUsedDiskSpace();

        $response->setContent(json_encode(array(
            'ram' => $ramInfos,
            'cpu' => $cpuInfos,
            'disk' => $diskSpace
        )));

        return $response;
    }
}
