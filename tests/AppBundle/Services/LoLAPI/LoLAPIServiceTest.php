<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Services\LoLAPI\LoLAPIService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

define('SUMMONER_ID', 29233320);
define('AHRI_ID', 103);
define('INVALID_CHAMPION_ID', 424242);
define('INVALID_SUMMONER_ID', 0000000);

class LoLAPIServiceTest extends KernelTestCase
{
    private $container = null;
    private $LoLAPIService = null;
    private $api_key = null;

    public function setUp()
    {
        static::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->api_key = $this->container->getParameter('riot_api_key');
        $this->LoLAPIService = new LoLAPIService($this->container);
    }

    // champion-v1.2

    public function testChampionApiShouldReturnAllChampions()
    {
        $champions = $this->LoLAPIService->getChampions();
        $this->assertTrue(array_key_exists('champions' ,$champions));
    }

    public function testChampionApiShouldNotBeNull()
    {
        $champions = $this->LoLAPIService->getChampions();
        $this->assertNotNull($champions);
    }

    public function testInvalidIdShouldNotReturnChampionInfo()
    {
        $champion = $this->LoLAPIService->getChampionById(INVALID_CHAMPION_ID);
        $this->assertFalse(array_key_exists('id' ,$champion));
    }

    public function testValidIdShouldReturnChampionInfo()
    {
        $champion = $this->LoLAPIService->getChampionById(AHRI_ID);
        $this->assertTrue(array_key_exists('id' ,$champion));
    }

    // championmastery

    public function testValidIdsShouldReturnChampionMasteryInfo()
    {
        $champion = $this->LoLAPIService->getChampionMasteryByChampionId(SUMMONER_ID, AHRI_ID);
        $this->assertTrue(array_key_exists('championPoints' ,$champion));
    }

    public function testInvalidChampionIdShouldNotReturnChampionMasteryInfo()
    {
        $champion = $this->LoLAPIService->getChampionMasteryByChampionId(SUMMONER_ID, INVALID_CHAMPION_ID);
        $this->assertFalse(array_key_exists('championPoints' ,$champion));
    }

    public function testInvalidSummonerIdShouldNotReturnChampionMasteryInfo()
    {
        $champion = $this->LoLAPIService->getChampionMasteryByChampionId(INVALID_CHAMPION_ID, AHRI_ID);
        $this->assertFalse(array_key_exists('championPoints' ,$champion));
    }


    // current-game-v1.0

    // featured-games-v1.0

    // game-v1.3

    // league-v2.5

    // lol-static-data-v1.2

    // lol-status-v1.0

    // match-v2.2

    // matchlist-v2.2

    // stats-v1.3

    // summoner-v1.4
}
