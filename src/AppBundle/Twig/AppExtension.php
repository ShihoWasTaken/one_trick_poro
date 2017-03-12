<?php

namespace AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends \Twig_Extension
{

    /** @var ContainerInterface */
    protected $container;

    /** @param ContainerInterface $container */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sumIcon', array($this, 'summonerIcon')),
        );
    }

    private function testIfURLExists($url)
    {
        $headers = @get_headers($url);
        if (strpos($headers[0], '404') === false) {
            return true;
        } else {
            return false;
        }
    }

    private function testIfURLExistsWithCURL($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $result = curl_exec($curl);
        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 404) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function summonerIcon($id)
    {
        $static_data_version = $this->container->getParameter('static_data_version');
        if ($this->testIfURLExists("http://ddragon.leagueoflegends.com/cdn/" . $static_data_version . "/img/profileicon/" . $id . ".png")) {
            return "http://ddragon.leagueoflegends.com/cdn/" . $static_data_version . "/img/profileicon/" . $id . ".png";
        } else {
            return "http://ddragon.leagueoflegends.com/cdn/" . $static_data_version . "/img/profileicon/" . 0 . ".png";
        }
    }
}