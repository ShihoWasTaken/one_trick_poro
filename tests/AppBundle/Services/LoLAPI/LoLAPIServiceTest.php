<?php

namespace AppBundle\Tests\Services\LoLAPI;

use AppBundle\Services\LoLAPI\LoLAPIService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

define('SUMMONER_ID', 29233320);
define('SUMMONER_NAME', 'Shiho');
define('SUMMONER_ID_RANKED', 20179047);
define('SUMMONER_NAME_RANKED', 20179047);
define('AHRI_ID', 103);
define('SPELL_ID', 1);
define('RUNE_ID', 5235);
define('MASTERY_ID', 6121);
define('ITEM_ID', 1410);
define('LOCALE_FR', 'fr_FR');
define('INVALID_CHAMPION_ID', 424242);
define('INVALID_SUMMONER_ID', 0000000);
define('INVALID_SUMMONER_NAME', 'azertgfggcbqqdd');
define('SHIHO_CAPS_LOCK', 'SHIHO');
define('SHIHO_LOWERCASE', 'shiho');
define('SPECIAL_CHAR_CAPS_LOCK_1', 'Árya');
define('SPECIAL_CHAR_LOWERCASE_1', 'árya');
define('SPECIAL_CHAR_CAPS_LOCK_2', '종학잉');
define('SPECIAL_CHAR_LOWERCASE_2', '종학잉');
define('SPECIAL_CHAR_CAPS_LOCK_3', 'obq sx pdo');
define('SPECIAL_CHAR_LOWERCASE_3', 'obqsxpdo');
define('REGION_SLUG', 'euw');


class LoLAPIServiceTest extends KernelTestCase
{
    private $container = null;

    /**
     * @var \AppBundle\Services\LoLAPI\LoLAPIService
     */
    private $LoLAPIService = null;
    private $api_key = null;
    private $static_data_version = null;

    /**
     * @var \AppBundle\Entity\StaticData\Region
     */
    private $region = null;

    public function setUp()
    {
        static::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->api_key = $this->container->getParameter('riot_api_key');
        $this->static_data_version = $this->container->getParameter('static_data_version');
        $em = $this->container->get('doctrine')->getManager();
        $region = $em->getRepository('AppBundle:StaticData\Region')->findOneBy([
            'slug' => REGION_SLUG
        ]);
        if($region == null)
        {
            throw new Exception('Region not existing');
        }
        $this->region = $region;
        $this->LoLAPIService = new LoLAPIService($this->container);
    }

    public function testToSafeLowerCase()
    {
        $lower = $this->LoLAPIService->toSafeLowerCase(SHIHO_CAPS_LOCK);
        $this->assertEquals(SHIHO_LOWERCASE , $lower, 'La fonction toSafeLowerCase(' . SHIHO_CAPS_LOCK . ') devrait retourner ' . SHIHO_LOWERCASE);

        $lower = $this->LoLAPIService->toSafeLowerCase(SPECIAL_CHAR_CAPS_LOCK_1);
        $this->assertEquals(SPECIAL_CHAR_LOWERCASE_1 , $lower, 'La fonction toSafeLowerCase(' . SPECIAL_CHAR_CAPS_LOCK_1 . ') devrait retourner ' . SPECIAL_CHAR_LOWERCASE_1);

        $lower = $this->LoLAPIService->toSafeLowerCase(SPECIAL_CHAR_CAPS_LOCK_2);
        $this->assertEquals(SPECIAL_CHAR_LOWERCASE_2 , $lower, 'La fonction toSafeLowerCase(' . SPECIAL_CHAR_CAPS_LOCK_2 . ') devrait retourner ' . SPECIAL_CHAR_LOWERCASE_2);

        $lower = $this->LoLAPIService->toSafeLowerCase(SPECIAL_CHAR_CAPS_LOCK_3);
        $this->assertEquals(SPECIAL_CHAR_LOWERCASE_3 , $lower, 'La fonction toSafeLowerCase(' . SPECIAL_CHAR_CAPS_LOCK_3 . ') devrait retourner ' . SPECIAL_CHAR_LOWERCASE_3);
    }

    /* Champion v1.2
     * Only 1 entry
     */

    public function testGetChampions()
    {
        $champions = $this->LoLAPIService->getChampions($this->region);
        $this->assertArrayHasKey('champions' , $champions, 'Le tableau retourné doit avoir une clé champions');
    }

    public function testGetChampionById()
    {
        $champion = $this->LoLAPIService->getChampionById($this->region, AHRI_ID);
        $this->assertArrayHasKey('id' , $champion, 'Un ID de champion valide devrait retourner des données');

        $champion = $this->LoLAPIService->getChampionById($this->region, INVALID_CHAMPION_ID);
        $this->assertArrayNotHasKey('id' , $champion, 'Un ID de champion invalide ne devrait pas retourner de données');
    }

    /* Champion Mastery
     * Only 1 entry
     */

    public function testGetChampionMasteryByChampionId()
    {
        $champion = $this->LoLAPIService->getChampionMasteryByChampionId($this->region, SUMMONER_ID, AHRI_ID);
        $this->assertArrayHasKey('championPoints' , $champion, 'Le tableau retourné doit avoir une clé ' . 'championPoints');

        $this->LoLAPIService->getChampionMasteryByChampionId($this->region, SUMMONER_ID, INVALID_CHAMPION_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(204, $responseCode, 'Le code de retour de la requête doit être 204 (pas de contenu)');

        $this->LoLAPIService->getChampionMasteryByChampionId($this->region, INVALID_SUMMONER_ID, AHRI_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(204, $responseCode, 'Le code de retour de la requête doit être 204 (pas de contenu)');
    }

    public function testGetChampionsMastery()
    {
        $champions = $this->LoLAPIService->getChampionsMastery($this->region, SUMMONER_ID);
        $this->assertTrue(array_key_exists('championPoints' ,$champions[0]), 'La liste retournée doit avoir une entrée ' . 'championPoints');

        $champions = $this->LoLAPIService->getChampionsMastery($this->region, INVALID_SUMMONER_ID);
        $this->assertEmpty($champions, 'Doit retourner un tableau vide pour un summoner inexistant');
    }

    public function testGetTotalMasteryScore()
    {
        $score = $this->LoLAPIService->getTotalMasteryScore($this->region, SUMMONER_ID);
        $this->assertGreaterThan(100, $score, 'Le score de ' . SUMMONER_NAME . ' doit être supérieur à 100');

        $score = $this->LoLAPIService->getTotalMasteryScore($this->region, INVALID_SUMMONER_ID);
        $this->assertEquals(0, $score, 'La requête doit retourner 0 pour un identifiant invalide');
    }

    public function testGetMasteryTopChampions()
    {
        $top3Champions = $this->LoLAPIService->getMasteryTopChampions($this->region, SUMMONER_ID);
        $this->assertCount(3, $top3Champions, 'Par défaut, on doit retourner exactement 3 champions');

        $top10Champions = $this->LoLAPIService->getMasteryTopChampions($this->region, SUMMONER_ID, 10);
        $this->assertCount(10, $top10Champions, 'En passant 10 en argument, on doit retourner exactement 10 champions');

        $invalid = $this->LoLAPIService->getMasteryTopChampions($this->region, INVALID_SUMMONER_ID);
        $this->assertEmpty($invalid, 'En passant un faux summoner ID, on doit retourner un tableau vide');

        $invalid = $this->LoLAPIService->getMasteryTopChampions($this->region, INVALID_SUMMONER_ID, 10);
        $this->assertEmpty($invalid, 'En passant 10 en argument et un faux summoner ID, on doit retourner un tableau vide');
    }

    /* Current Game v1.0
     * Only 1 entry
     */

    public function testGetCurrentGame()
    {
        $this->LoLAPIService->getCurrentGame($this->region, SUMMONER_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour un joueur qui n\'est pas en game');

        $featured = $this->LoLAPIService->getFeaturedGames($this->region);
        $summonerName = $featured['gameList'][0]['participants'][0]['summonerName'];
        $summoner = $this->LoLAPIService->getSummonerByNames($this->region, array($summonerName));
        $InGamesummonerID = $summoner[$this->LoLAPIService->toSafeLowerCase($summonerName)]['id'];
        $data = $this->LoLAPIService->getCurrentGame($this->region, $InGamesummonerID);
        $this->assertArrayHasKey('gameId', $data, 'Les données retournées doivent comporter l\'information gameId');
        
    }

    /* Featured Games v1.0
     * Only 1 entry
     */
    public function testGetFeaturedGames()
    {
        $this->LoLAPIService->getFeaturedGames($this->region);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200');
    }
    
    /* Game v1.3
     * Only 1 entry
     */
    public function testGetRecentGames()
    {
        $games = $this->LoLAPIService->getRecentGames($this->region, SUMMONER_ID);
        $this->assertArrayHasKey("games", $games, 'Les données retournées doivent comporter l\'information games');

        $this->LoLAPIService->getRecentGames($this->region, INVALID_SUMMONER_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour un summoner ID invalide');
    }

    /* League v2.5
     * Max 10 entries
     */
    public function testGetLeaguesBySumonnerIds()
    {
        $this->LoLAPIService->getLeaguesBySumonnerIds($this->region, array(INVALID_SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour un summoner ID invalide');

        $leagues = $this->LoLAPIService->getLeaguesBySumonnerIds($this->region, array(SUMMONER_ID_RANKED));
        $this->assertArrayHasKey(strval(SUMMONER_ID_RANKED), $leagues, 'Les données retournées doivent comporter une clé avec le summoner ID passé en paramètre');
    }

    public function testGetLeaguesBySumonnerIdsEntry()
    {
        $this->LoLAPIService->getLeaguesBySumonnerIdsEntry($this->region, array(INVALID_SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour un summoner ID invalide');

        $leagues = $this->LoLAPIService->getLeaguesBySumonnerIdsEntry($this->region, array(SUMMONER_ID_RANKED));
        $this->assertArrayHasKey(strval(SUMMONER_ID_RANKED), $leagues, 'Les données retournées doivent comporter une clé avec le summoner ID passé en paramètre');
    }
    
    /* Lol Static Data v1.2
     * No rate limit
     */

    public function testGetStaticDataChampions()
    {
        $this->LoLAPIService->getStaticDataChampions($this->region, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticDataChampionById()
    {
        $this->LoLAPIService->getStaticDataChampionById($this->region, AHRI_ID,LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticDataItems()
    {
        $this->LoLAPIService->getStaticDataItems($this->region, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticDataItemById()
    {
        $this->LoLAPIService->getStaticDataItemById($this->region, ITEM_ID, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticLanguageStrings()
    {
        $this->LoLAPIService->getStaticLanguageStrings($this->region);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticLanguages()
    {
        $this->LoLAPIService->getStaticLanguages($this->region);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticMap()
    {
        $this->LoLAPIService->getStaticMap($this->region, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticMasteries()
    {
        $this->LoLAPIService->getStaticMasteries($this->region, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticMasteryById()
    {
        $this->LoLAPIService->getStaticMasteryById($this->region, MASTERY_ID, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticRealm()
    {
        $data = $this->LoLAPIService->getStaticDataVersions($this->region);
        $lastVersion = $data[0];
        $data = $this->LoLAPIService->getStaticRealm($this->region);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertEquals($lastVersion, $data['v'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['dd'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['lg'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['champion'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['profileicon'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['item'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['map'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['mastery'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['language'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['summoner'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['n']['rune'], 'La version doit être égale à ' . $lastVersion);
        $this->assertEquals($lastVersion, $data['css'], 'La version doit être égale à ' . $lastVersion);
    }

    public function testGetStaticRunes()
    {
        $this->LoLAPIService->getStaticRunes($this->region, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticRuneById()
    {
        $this->LoLAPIService->getStaticRuneById($this->region, RUNE_ID, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticSummonerSpells()
    {
        $this->LoLAPIService->getStaticSummonerSpells($this->region, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function testGetStaticSummonerSpellById()
    {
        $this->LoLAPIService->getStaticSummonerSpellById($this->region, SPELL_ID, LOCALE_FR);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }


    public function testGetStaticDataVersions()
    {
        $data = $this->LoLAPIService->getStaticDataVersions($this->region);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertEquals($this->static_data_version, $data[0], 'static_data_versionde parameters.yml doit être la dernière version en date');
    }

    /* Lol Status v1.0
     * No rate limit
     */

    public function testGetShards()
    {
        $this->LoLAPIService->getShards();
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    /**
     * @dataProvider shardProvider
     */
    public function testGetShardByRegion($shard)
    {
        $this->LoLAPIService->getShardByRegion($shard);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
    }

    public function shardProvider()
    {
        return array(
            array('na'),
            array('br'),
            array('lan'),
            array('las'),
            array('oce'),
            array('eune'),
            array('tr'),
            array('ru'),
            array('euw'),
            array('kr')
        );
    }


    /* Match v2.2
     * Only 1 entry
     */

    public function testGetMatch()
    {
        $games = $this->LoLAPIService->getRecentGames($this->region, SUMMONER_ID);
        $gameID = $games['games'][0]['gameId'];

        $data = $this->LoLAPIService->getMatch($this->region, $gameID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées et pour la game ' . $gameID);
        $this->assertArrayNotHasKey('timeline', $data, 'Les données retournées ne doivent pas comporter une clé timeline');

        $data = $this->LoLAPIService->getMatch($this->region, $gameID, true);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey('timeline', $data, 'Les données retournées doivent comporter une clé timeline');

        $data = $this->LoLAPIService->getMatch($this->region, $gameID, false);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayNotHasKey('timeline', $data, 'Les données retournées ne doivent pas comporter une clé timeline');
    }
    
    /* Matchlist v2.2
     * Only 1 entry
     */

    //TODO: paramètres optionnels getMatchlist($id, $championId = null, $rankedQueues = null, $seasons = null, $beginTime = null, $endTime = null, $beginIndex = null, $endIndex = null)
    public function testGetMatchList()
    {
        $this->LoLAPIService->getMatchlist($this->region, INVALID_SUMMONER_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour un summoner ID invalide');

        $games = $this->LoLAPIService->getMatchlist($this->region, SUMMONER_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey('matches', $games, 'Les données retournées doivent comporter une clé matches');
    }

    /* Stats v1.3
     * Only 1 entry
     */
    public function testGetSeasonCode()
    {
        $season = $this->LoLAPIService->getSeasonCode();
        $this->assertEquals('SEASON2017', $season, 'La saison retournée doit être SEASON2017');

        $season = $this->LoLAPIService->getSeasonCode(null);
        $this->assertEquals('SEASON2017', $season, 'La saison retournée doit être SEASON2017');

        $season = $this->LoLAPIService->getSeasonCode(0);
        $this->assertEquals('SEASON2017', $season, 'La saison retournée doit être SEASON2017');

        $season = $this->LoLAPIService->getSeasonCode(10);
        $this->assertEquals('SEASON2017', $season, 'La saison retournée doit être SEASON2017');

        $season = $this->LoLAPIService->getSeasonCode(7);
        $this->assertEquals('SEASON2017', $season, 'La saison retournée doit être SEASON2017');

        $season = $this->LoLAPIService->getSeasonCode(6);
        $this->assertEquals('SEASON2016', $season, 'La saison retournée doit être SEASON2016');

        $season = $this->LoLAPIService->getSeasonCode(5);
        $this->assertEquals('SEASON2015', $season, 'La saison retournée doit être SEASON2015');

        $season = $this->LoLAPIService->getSeasonCode(4);
        $this->assertEquals('SEASON2014', $season, 'La saison retournée doit être SEASON2014');

        $season = $this->LoLAPIService->getSeasonCode(3);
        $this->assertEquals('SEASON3', $season, 'La saison retournée doit être SEASON3');
    }

    public function testGetRankedStatsBySummonerId()
    {
        $data = $this->LoLAPIService->getRankedStatsBySummonerId($this->region, SUMMONER_ID_RANKED);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey('champions', $data, 'Les données retournées doivent comporter une clé champions');

        $this->LoLAPIService->getRankedStatsBySummonerId($this->region, INVALID_SUMMONER_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour un summoner invalide');
    }

    public function testGetSummaryStatsBySummonerId()
    {
        $data = $this->LoLAPIService->getSummaryStatsBySummonerId($this->region, SUMMONER_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey('playerStatSummaries', $data, 'Les données retournées doivent comporter une clé playerStatSummaries');

        $this->LoLAPIService->getSummaryStatsBySummonerId($this->region, INVALID_SUMMONER_ID);
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour un summoner invalide');
    }

    /* Summoner v1.4
     * Array of max 40 entries
     */
    public function testGetSummonerByNames()
    {
        $data = $this->LoLAPIService->getSummonerByNames($this->region, array(SUMMONER_NAME));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $safeLowerName = $this->LoLAPIService->toSafeLowerCase(SUMMONER_NAME);
        $this->assertArrayHasKey($safeLowerName, $data, 'Les données retournées doivent comporter une clé ' . $safeLowerName);

        $this->LoLAPIService->getSummonerByNames($this->region, array(INVALID_SUMMONER_NAME));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour ces métadonnées');
    }

    public function testGetSummonerByIds()
    {
        $data = $this->LoLAPIService->getSummonerByIds($this->region, array(SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey(strval(SUMMONER_ID), $data, 'Les données retournées doivent comporter une clé ' . SUMMONER_ID);

        $this->LoLAPIService->getSummonerByIds($this->region, array(INVALID_SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour ces métadonnées');
    }

    public function testGetMasteriesBySummonerIds()
    {
        $data = $this->LoLAPIService->getMasteriesBySummonerIds($this->region, array(SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey('masteries', $data[strval(SUMMONER_ID)]['pages'][0], 'Les données retournées doivent comporter une clé masteries');

        $this->LoLAPIService->getMasteriesBySummonerIds($this->region, array(INVALID_SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour ces métadonnées');
    }

    public function testGetNamesBySummonerIds()
    {
        $data = $this->LoLAPIService->getNamesBySummonerIds($this->region, array(SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey(strval(SUMMONER_ID), $data, 'Les données retournées doivent comporter une clé ' . SUMMONER_ID);
        $this->assertEquals(SUMMONER_NAME, $data[strval(SUMMONER_ID)], 'Le nom retourné doit être ' . SUMMONER_NAME);

        $this->LoLAPIService->getNamesBySummonerIds($this->region, array(INVALID_SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour ces métadonnées');
    }

    public function testGetRunesBySummonerIds()
    {
        $data = $this->LoLAPIService->getRunesBySummonerIds($this->region, array(SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(200, $responseCode, 'Le code de retour de la requête doit être 200 pour ces métadonnées');
        $this->assertArrayHasKey('slots', $data[strval(SUMMONER_ID)]['pages'][0], 'Les données retournées doivent comporter une clé slots');

        $this->LoLAPIService->getRunesBySummonerIds($this->region, array(INVALID_SUMMONER_ID));
        $responseCode = $this->LoLAPIService->getResponseCode();
        $this->assertEquals(404, $responseCode, 'Le code de retour de la requête doit être 404 pour ces métadonnées');
    }
}
