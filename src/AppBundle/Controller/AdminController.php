<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\LoLAPI\LoLAPIService;

class AdminController extends Controller
{

    public function updateChampionsAction()
    {
        $staticDataUpdateService = $this->container->get('app.staticdataupdate');
        $staticDataUpdateService->updateChampions();
        return new Response();
    }
}
