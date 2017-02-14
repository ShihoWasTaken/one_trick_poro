<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoLAPIController extends Controller
{
    public function profileAction($userId)
    {
        $static_data_version = $this->container->getParameter('static_data_version');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if($user != 'anon.')
        {
            if($user->getId() != $userId)
            {
                $em = $this->container->get('doctrine')->getManager();
                $user =  $em->getRepository('AppBundle:User')->findOneById($userId);
            }
        }
        else
        {
            $em = $this->container->get('doctrine')->getManager();
            $user =  $em->getRepository('AppBundle:User')->findOneById($userId);
        }
        $gravatar = $this->getGravatar("kenny.guiougou@gmail.com");
        return $this->render('AppBundle:Account:profile.html.twig',
            array(
                'user' => $user,
                'static_data_version' => $static_data_version,
                'gravatar' => $gravatar
            ));
    }


    public function editProfileAction($userId)
    {
        $static_data_version = $this->container->getParameter('static_data_version');
        $api = $this->container->get('app.lolapi');
        $sum = $this->container->get('app.lolsummoner');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $api = $this->container->get('app.lolapi');
        $region = $sum->getRegionBySlug('euw');
        $data = $api->getSummonerByNames($region, array('Shiho', 'Mikami Teru'));
        $sum = $this->container->get('app.lolsummoner');
        return $this->render('AppBundle:Account:profile_edit.html.twig',
            array(
                'data' => $data,
                'static_data_version' => $static_data_version,
            ));
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string $email The email address
     * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boole $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source https://gravatar.com/site/implement/images/php/
     */
    private function getGravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }
}
