<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoLAPIController extends Controller
{
    public function profileAction($userId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user != 'anon.') {
            if ($user->getId() != $userId) {
                $em = $this->container->get('doctrine')->getManager();
                $user = $em->getRepository('AppBundle:User')->findOneById($userId);
            }
        } else {
            $em = $this->container->get('doctrine')->getManager();
            $user = $em->getRepository('AppBundle:User')->findOneById($userId);
        }
        return $this->render('AppBundle:Account:profile.html.twig',
            array(
                'user' => $user
            ));
    }


    public function editProfileAction($userId)
    {
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $api = $this->container->get('app.lolapi');
        $region = $sum->getRegionBySlug('euw');
        $data = $api->getSummonerByNames($region, array('Shiho', 'Mikami Teru'));
        $sum = $this->container->get('app.lolsummoner');
        return $this->render('AppBundle:Account:profile_edit.html.twig',
            array(
                'data' => $data
            ));
    }
}
