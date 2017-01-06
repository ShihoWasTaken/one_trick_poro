<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class StaticDataController extends Controller
{
    public function updateChampionsAction()
    {
        $staticDataUpdateService = $this->container->get('app.staticdataupdate');
        $staticDataUpdateService->updateChampions();
        return new Response();
    }
}
