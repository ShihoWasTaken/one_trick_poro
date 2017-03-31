<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Monolog\Logger;

class AppExtension extends \Twig_Extension
{
    /**
     * @var Logger
     */
    protected $logger;
    protected $static_data_version;

    public function __construct(Logger $logger, $static_data_version)
    {
        $this->logger = $logger;
        $this->static_data_version = $static_data_version;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sumIcon', array($this, 'summonerIcon')),
            new \Twig_SimpleFilter('duration', array($this, 'durationToMinutesAndSeconds')),
            new \Twig_SimpleFilter('subType', array($this, 'subType')),
            new \Twig_SimpleFilter('mapName', array($this, 'mapName')),
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
        if ($this->testIfURLExists("http://ddragon.leagueoflegends.com/cdn/" . $this->static_data_version . "/img/profileicon/" . $id . ".png")) {
            return "http://ddragon.leagueoflegends.com/cdn/" . $this->static_data_version . "/img/profileicon/" . $id . ".png";
        } else {
            return "http://ddragon.leagueoflegends.com/cdn/" . $this->static_data_version . "/img/profileicon/" . 0 . ".png";
        }
    }

    public function durationToMinutesAndSeconds($duration)
    {
        $minutes = floor($duration / 60);
        $seconds = $duration % 60;;
        return $minutes . ":" . $seconds;
    }

    public function subType($subType)
    {
        switch ($subType) {
            case 'NONE':
                return "Custom game";
            case 'NORMAL':
                return "Normal game";
            case 'NORMAL_3x3':
                return "Normal game 3v3";
            case 'ODIN_UNRANKED':
                return "Dominion";
            case 'ARAM_UNRANKED_5x5':
                return "ARAM";
            case 'BOT':
                return "Coop vs AI";
            case 'BOT_3x3':
                return "Coop vs AI 3v3";
            case 'RANKED_SOLO_5x5':
                return "Ranked Solo/Duo";
            case 'RANKED_TEAM_3x3':
                return "Ranked team 3v3";
            case 'RANKED_TEAM_5x5':
                return "Ranked team 5v5";
            case 'ONEFORALL_5x5':
                return "One For All";
            case 'FIRSTBLOOD_1x1':
                return "Firstblood 1v1";
            case 'FIRSTBLOOD_2x2':
                return "Firstblood 2v2";
            case 'SR_6x6':
                return "Hexakill";
            case 'CAP_5x5':
                return "Teambuilder";
            case 'URF':
                return "URF";
            case 'URF_BOT':
                return "URF vs AI";
            case 'NIGHTMARE_BOT':
                return "Nightmare bots";
            case 'ASCENSION':
                return "Ascension";
            case 'HEXAKILL':
                return "Hexakill";
            case 'KING_PORO':
                return "King poro";
            case 'COUNTER_PICK':
                return "Nemesis";
            case 'BILGEWATER':
                return "Bilgewater";
            case 'SIEGE':
                return "Nexus siege";
            case 'RANKED_FLEX_TT':
                return "Ranked flex 3v3";
            case 'RANKED_FLEX_SR':
                return "Ranked flex 5v5";
            default:
                $this->logger->warning("L'id de subType " . $subType . " a été demandé mais ne corresponds à aucun mode de jeu repertorié.");
                return "";
        }
    }

    public function mapName($id)
    {
        switch ($id) {
            case 1:
            case 2:
            case 11:
                return "Summoner's Rift";
            case 3:
                return "The Proving Grounds";
            case 4:
            case 10:
                return "Twisted Treeline";
            case 8:
                return "The Crystal Scar";
            case 12:
                return "Howling Abyss";
            case 14:
                return "Butcher's Bridge";
            default:
                $this->logger->warning("L'id de map " . $id . " a été demandé mais ne corresponds à aucune map repertoriée.");
                return "";
        }
    }
}