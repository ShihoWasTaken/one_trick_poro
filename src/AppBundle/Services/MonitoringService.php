<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Services\LoLAPI\LoLAPIService;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;


class MonitoringService
{
    /**
     * @var \AppBundle\Services\LoLAPI\LoLAPIService
     */
    private $api;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct(Logger $logger, EntityManager $entityManager, LoLAPIService $api)
    {
        $this->logger = $logger;
        $this->api = $api;
        $this->em = $entityManager;
    }

    public function getIpAdress()
    {
        $client  = isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:null;
        $forward = isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:null;
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        else if(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;

    }

    public function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE)
    {
        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city" => @$ipdat->geoplugin_city,
                            "state" => @$ipdat->geoplugin_regionName,
                            "country" => @$ipdat->geoplugin_countryName,
                            "country_code" => @$ipdat->geoplugin_countryCode,
                            "continent" => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }
        return $output;
    }

    public function getRamInfos()
    {
        $data = shell_exec('cat /proc/meminfo');
        $lines = explode(PHP_EOL, $data);
        $totalInKB = explode(':', trim($lines[0]));
        $totalInKB = substr($totalInKB[1], 0, -3);
        // MemAvailable
        $availableInKB = explode(':', trim($lines[2]));
        $availableInKB = substr($availableInKB[1], 0, -3);
        $usedInKB = $totalInKB - $availableInKB;
        $percent = round(($usedInKB / $totalInKB) * 100, 0);

        return array(
            'total' => $totalInKB,
            'available' => $availableInKB,
            'used' => $usedInKB,
            'percent' => $percent
        );
    }

    public function getCPULoad()
    {
        $data = shell_exec("mpstat | grep all | awk '{print 100 - $12}'");
        return trim($data);
    }

    public function getTotalUsedDiskSpace()
    {
        $data = shell_exec("df / | grep \"/\" | awk '{print $5}'");
        return substr(trim($data), 0, -1);
    }

    public function getNetworkPercentage()
    {
        $data = shell_exec("ifconfig | grep bytes | awk 'NR==2'");
        $exploded = explode(' ', trim($data));
        $download = substr($exploded[2], 1) . ' ' . substr($exploded[3], 0, -1);
        $upload = substr($exploded[7], 1) . ' ' . substr($exploded[8], 0, -1);
        return array(
            'download' => $download,
            'upload' => $upload
        );
    }

}
