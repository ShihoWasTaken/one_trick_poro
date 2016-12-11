<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\LoLAPI\LoLAPIService;

class StaticDataController extends Controller
{
    public function updateChampionsAction()
    {
        $staticDataUpdateService = $this->container->get('app.staticdataupdate');
        $staticDataUpdateService->updateChampions();
        return new Response();
    }

    public function editProfileAction($userId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $data = $api->getSummonerByNames(array('Shiho', 'Mikami Teru'));
        $sum = $this->container->get('app.lolsummoner');
        return $this->render('AppBundle:Account:profile_edit.html.twig',
            array(
                'data' => $data,
            ));
    }
}
