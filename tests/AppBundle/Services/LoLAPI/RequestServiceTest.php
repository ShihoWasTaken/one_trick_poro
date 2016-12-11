<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AppBundle\Services\LoLAPI\RequestService;

define('URL', 'https://euw.api.pvp.net/api/lol/euw/v1.2/champion/1?api_key=33ba84f4-70f1-4676-b1a6-d98a4e68609c');

class RequestServiceTest extends KernelTestCase
{
    private static $api_key = null;
    private static $container = null;
    private static $requestService = null;

    public function setUp()
    {
        // On utilise les attributs static pour ne set qu'une seule fois et éviter trop de requête à l'API
        if(static::$requestService == null)
        {
            static::bootKernel();
            static::$container = static::$kernel->getContainer();
            static::$api_key = static::$container->getParameter('riot_api_key');
            static::$requestService = new RequestService(static::$container);
            static::$requestService->request(URL);
        }
    }

    public function testApiKeyShouldBeValid()
    {
        $this->assertNotNull(static::$api_key);
    }

    public function testCurlShouldReturnCode200()
    {
        $this->assertEquals(200, static::$requestService->getResponseCode());
    }

    public function testCurlShouldNotReturnCode400()
    {
        $this->assertNotEquals(400, static::$requestService->getResponseCode());
    }

    public function testCurlShouldNotReturnCode401()
    {
        $this->assertNotEquals(401, static::$requestService->getResponseCode());
    }

    public function testCurlShouldNotReturnCode403()
    {
        $this->assertNotEquals(403, static::$requestService->getResponseCode());
    }

    public function testCurlShouldNotReturnCode404()
    {
        $this->assertNotEquals(404, static::$requestService->getResponseCode());
    }

    public function testCurlShouldNotReturnCode429()
    {
        $this->assertNotEquals(429, static::$requestService->getResponseCode());
    }

    public function testCurlShouldNotReturnCode500()
    {
        $this->assertNotEquals(500, static::$requestService->getResponseCode());
    }

    public function testCurlShouldNotReturnCode503()
    {
        $this->assertNotEquals(503, static::$requestService->getResponseCode());
    }

    public function testCurlShouldReturnHeaders()
    {
        $this->assertNotNull(static::$requestService->getHeaders());
    }

    public function testCurlShouldReturnJson()
    {
        $this->assertNotNull(static::$requestService->getResponse());
    }

    public function testCurlShouldReturnJsonToArray()
    {
        $this->assertNotNull(static::$requestService->getJSONResponseToArray());
    }
}
