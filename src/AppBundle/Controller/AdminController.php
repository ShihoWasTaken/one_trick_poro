<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{

    public function updateChampionsAction()
    {
        $staticDataUpdateService = $this->container->get('app.staticdataupdate');
        $staticDataUpdateService->updateChampions();
        return new Response();
    }

    public function updateRunesAction()
    {
        $staticDataUpdateService = $this->container->get('app.staticdataupdate');
        $staticDataUpdateService->updateRunes();
        return new Response();
    }

    public function updateMasteriesAction()
    {
        $staticDataUpdateService = $this->container->get('app.staticdataupdate');
        $staticDataUpdateService->updateMasteries();
        return new Response();
    }
}
