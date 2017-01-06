<?php

namespace AppBundle\Services\LoLAPI;

define('HTTPS', 'https://');
define('CHAMPION_API_VERSION', '/v1.2');
define('GAME_API_VERSION', '/v1.3');
define('LEAGUE_API_VERSION', '/v2.5');
define('STATIC_DATA_API_VERSION', '/v1.2');
define('MATCH_API_VERSION', '/v2.2');
define('MATCHLIST_API_VERSION', '/v2.2');
define('STATS_API_VERSION', '/v1.3');
define('SUMMONER_API_VERSION', '/v1.4');

use AppBundle\Services\CurlHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class LoLAPIService extends RequestService
{
	private $container;
	private $api_key;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->api_key = $this->container->getParameter('riot_api_key');
	}

	public function toSafeLowerCase($string)
	{
		$string = str_replace(' ', '', $string);
		return mb_strtolower($string, 'UTF-8');
	}

	/* Champion v1.2
     * Only 1 entry
     */

	public function getChampions(\AppBundle\Entity\StaticData\Region $region, $freeToPlay = false)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($freeToPlay !== false)
		{
			$optional_parameters[] = 'freeToPlay=true';
		}

		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug() . CHAMPION_API_VERSION . '/champion?'  . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getChampionById(\AppBundle\Entity\StaticData\Region $region, $championId)
	{
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug() . CHAMPION_API_VERSION . '/champion/'. $championId . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Champion Mastery
     * Only 1 entry
     */

	private function getPlatformIdByRegion($slug)
	{
		switch(strtoupper($slug))
		{
			case 'BR':
				return 'BR1';
				break;
			case 'EUNE':
				return 'EUN1';
				break;
			case 'EUW':
				return 'EUW1';
				break;
			case 'JP':
				return 'JP1';
				break;
			case 'KR':
				return 'KR';
				break;
			case 'LAN':
				return 'LA1';
				break;
			case 'LAS':
				return 'LA2';
				break;
			case 'NA':
				return 'NA1';
				break;
			case 'OCE':
				return 'OC1';
				break;
			case 'TR':
				return 'TR1';
				break;
			case 'RU':
				return 'RU';
				break;
		}
	}

	public function getChampionMasteryByChampionId(\AppBundle\Entity\StaticData\Region $region, $summonerId, $championId)
	{
		$platformId = $this->getPlatformIdByRegion($region->getSlug());
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/championmastery/location/' . $platformId . '/player/' . $summonerId . '/champion/' . $championId . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getChampionsMastery(\AppBundle\Entity\StaticData\Region $region, $summonerId)
	{
		$platformId = $this->getPlatformIdByRegion($region->getSlug());
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/championmastery/location/' . $platformId . '/player/' . $summonerId . '/champions' . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getTotalMasteryScore(\AppBundle\Entity\StaticData\Region $region, $summonerId)
	{
		$platformId = $this->getPlatformIdByRegion($region->getSlug());
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/championmastery/location/' . $platformId . '/player/' . $summonerId . '/score' . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getMasteryTopChampions(\AppBundle\Entity\StaticData\Region $region, $summonerId, $count = 3)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($count != 3)
		{
			$optional_parameters[] = 'count=' . $count;
		}
		$platformId = $this->getPlatformIdByRegion($region->getSlug());
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/championmastery/location/' . $platformId . '/player/' . $summonerId . '/topchampions?' . implode('&',$optional_parameters);
		return $this->request($url);
	}


	/* Current Game v1.0
     * Only 1 entry
     */

	public function getCurrentGame(\AppBundle\Entity\StaticData\Region $region, $summonerId)
	{
		$platformId = $this->getPlatformIdByRegion($region->getSlug());
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/observer-mode/rest/consumer/getSpectatorGameInfo/' . $platformId . '/' . $summonerId . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Featured Games v1.0
     * Only 1 entry
     */

	public function getFeaturedGames(\AppBundle\Entity\StaticData\Region $region)
	{
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/observer-mode/rest/featured' . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Game v1.3
     * Only 1 entry
     */

	public function getRecentGames(\AppBundle\Entity\StaticData\Region $region, $summonerId)
	{
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/api/lol/' . $region->getSlug() . GAME_API_VERSION . '/game/by-summoner/' . $summonerId . '/recent?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* League v2.5
     * Max 10 entries
     */

	public function getLeaguesBySumonnerIds(\AppBundle\Entity\StaticData\Region $region, Array $summonersIds)
	{
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/api/lol/' . $region->getSlug() . LEAGUE_API_VERSION . '/league/by-summoner/' . join(',', $summonersIds) .'?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getLeaguesBySumonnerIdsEntry(\AppBundle\Entity\StaticData\Region $region, Array $summonersIds)
	{
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/api/lol/' . $region->getSlug() . LEAGUE_API_VERSION . '/league/by-summoner/' . join(',', $summonersIds) .'/entry?api_key=' . $this->api_key;
		return $this->request($url);
	}

	private function getLeagueChallenger(\AppBundle\Entity\StaticData\Region $region, $queue)
	{
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/api/lol/' . $region->getSlug() . LEAGUE_API_VERSION . '/league/challenger?type=' . $queue .'&api_key='. $this->api_key;
		return $this->request($url);
	}

	public function getLeagueChallengerSoloQueue(\AppBundle\Entity\StaticData\Region $region)
	{
		$this->getLeagueChallenger($region, 'RANKED_SOLO_5x5');
	}

	public function getLeagueChallengerRanked5v5(\AppBundle\Entity\StaticData\Region $region)
	{
		$this->getLeagueChallenger($region, 'RANKED_TEAM_5x5');
	}

	public function getLeagueChallengerRanked3v3(\AppBundle\Entity\StaticData\Region $region)
	{
		$this->getLeagueChallenger($region, 'RANKED_TEAM_3x3');
	}

	private function getLeagueMaster(\AppBundle\Entity\StaticData\Region $region, $queue)
	{
		$url = HTTPS . $region->getSlug() . '.api.pvp.net/api/lol/' . $region->getSlug() . LEAGUE_API_VERSION . '/league/master?type=' . $queue .'&api_key='. $this->api_key;
		return $this->request($url);
	}

	public function getLeagueMasterSoloQueue(\AppBundle\Entity\StaticData\Region $region)
	{
		$this->getLeagueMaster($region, 'RANKED_SOLO_5x5');
	}

	public function getLeagueMasterRanked5v5(\AppBundle\Entity\StaticData\Region $region)
	{
		$this->getLeagueMaster($region, 'RANKED_TEAM_5x5');
	}

	public function getLeagueMasterRanked3v3(\AppBundle\Entity\StaticData\Region $region)
	{
		$this->getLeagueMaster($region, 'RANKED_TEAM_3x3');
	}


	/* Lol Static Data v1.2
     * No rate limit
     */

	public function getStaticDataChampions(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $dataById = null, $champData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($dataById !== false)
		{
			$optional_parameters[] = 'dataById=' . $dataById;
		}
		if($champData !== false)
		{
			$optional_parameters[] = 'champData=' . $champData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/champion?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticDataChampionById(\AppBundle\Entity\StaticData\Region $region, $championId, $locale = null, $version = null, $champData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($champData !== false)
		{
			$optional_parameters[] = 'champData=' . $champData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/champion/' . $championId . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticDataItems(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $dataById = null, $itemListData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($dataById !== false)
		{
			$optional_parameters[] = 'dataById=' . $dataById;
		}
		if($itemListData !== false)
		{
			$optional_parameters[] = 'itemListData=' . $itemListData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/item?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticDataItemById(\AppBundle\Entity\StaticData\Region $region, $itemId, $locale = null, $version = null, $itemListData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($itemListData !== false)
		{
			$optional_parameters[] = 'itemListData=' . $itemListData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/item/' . $itemId . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticLanguageStrings(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/language-strings?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticLanguages(\AppBundle\Entity\StaticData\Region $region)
	{
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/languages?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getStaticMap(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/map?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticMasteries(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $masteryListData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($masteryListData !== false)
		{
			$optional_parameters[] = 'masteryListData=' . $masteryListData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/mastery?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticMasteryById(\AppBundle\Entity\StaticData\Region $region, $id, $locale = null, $version = null, $masteryListData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($masteryListData !== false)
		{
			$optional_parameters[] = 'masteryListData=' . $masteryListData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/mastery/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticRealm(\AppBundle\Entity\StaticData\Region $region)
	{
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/realm?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getStaticRunes(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $runeListData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($runeListData !== false)
		{
			$optional_parameters[] = 'runeListData=' . $runeListData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/rune?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticRuneById(\AppBundle\Entity\StaticData\Region $region, $id, $locale = null, $version = null, $runeListData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($runeListData !== false)
		{
			$optional_parameters[] = 'runeListData=' . $runeListData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/rune/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticSummonerSpells(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $spellData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($spellData !== false)
		{
			$optional_parameters[] = 'spellData=' . $spellData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/summoner-spell?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticSummonerSpellById(\AppBundle\Entity\StaticData\Region $region, $id, $locale = null, $version = null, $spellData = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($locale !== false)
		{
			$optional_parameters[] = 'locale=' . $locale;
		}
		if($version !== false)
		{
			$optional_parameters[] = 'version=' . $version;
		}
		if($spellData !== false)
		{
			$optional_parameters[] = 'spellData=' . $spellData;
		}
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/summoner-spell/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}


	public function getStaticDataVersions(\AppBundle\Entity\StaticData\Region $region)
	{
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region->getSlug() . '/v1.2/versions?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Lol Status v1.0
     * No rate limit
     */

	public function getShards()
	{
		$url = 'http://status.leagueoflegends.com/shards';
		return $this->request($url);
	}

	public function getShardByRegion($platformId)
	{
		$url = 'http://status.leagueoflegends.com/shards/' . $platformId;
		return $this->request($url);
	}

	/* Match v2.2
     * Only 1 entry
     */

	public function getMatch(\AppBundle\Entity\StaticData\Region $region, $id, $includeTimeline = false)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($includeTimeline !== false)
		{
			$optional_parameters[] = 'includeTimeline=true';
		}
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v2.2/match/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	/* Matchlist v2.2
     * Only 1 entry
     */

	public function getMatchlist(\AppBundle\Entity\StaticData\Region $region, $id, $championId = null, $rankedQueues = null, $seasons = null, $beginTime = null, $endTime = null, $beginIndex = null, $endIndex = null)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($championId !== null)
		{
			$optional_parameters[] = 'championId=' . $championId;
		}
		if($rankedQueues !== null)
		{
			$optional_parameters[] = 'rankedQueues=' . $rankedQueues;
		}
		if($seasons !== null)
		{
			$optional_parameters[] = 'seasons=' . $seasons;
		}
		if($beginTime !== null)
		{
			$optional_parameters[] = 'beginTime=' . $beginTime;
		}
		if($endTime !== null)
		{
			$optional_parameters[] = 'endTime=' . $endTime;
		}
		if($beginIndex !== null)
		{
			$optional_parameters[] = 'beginIndex=' . $beginIndex;
		}
		if($endIndex !== null)
		{
			$optional_parameters[] = 'endIndex=' . $endIndex;
		}

		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v2.2/matchlist/by-summoner/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}


	/* Stats v1.3
     * Only 1 entry
     */

	public function getSeasonCode($season = 7)
	{
		switch($season)
		{
			case 3;
				$season = 'SEASON3';
				break;
			case 4:
				$season = 'SEASON2014';
				break;
			case 5:
				$season = 'SEASON2015';
				break;
			case 6:
				$season = 'SEASON2016';
				break;
			case 7:
			default:
				$season = 'SEASON2017';
				break;
		}
		return $season;
	}

	public function getRankedStatsBySummonerId(\AppBundle\Entity\StaticData\Region $region, $id, $season = null)
	{
		if(isset($season))
		{
			$season = $this->getSeasonCode($season);
		}
		else
		{
			$season = $this->getSeasonCode();
		}
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v1.3/stats/by-summoner/' . $id.  '/ranked?season=' . $season . '&api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getSummaryStatsBySummonerId(\AppBundle\Entity\StaticData\Region $region, $id, $season = null)
	{
		if(isset($season))
		{
			$season = $this->getSeasonCode($season);
		}
		else
		{
			$season = $this->getSeasonCode();
		}
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v1.3/stats/by-summoner/' . $id.  '/summary?season=' . $season . '&api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Summoner v1.4
     * Array of max 40 entries
     */
	public function getSummonerByNames(\AppBundle\Entity\StaticData\Region $region, Array $names)
	{
		foreach($names as $name)
		{
			$name = strtolower($name);
			$name = str_replace(' ', '', $name);
		}
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v1.4/summoner/by-name/' . join(',', $names) .  '?api_key=' . $this->api_key;
		$url = str_replace(' ', '', $url);
		return $this->request($url);
	}

	public function getSummonerByIds(\AppBundle\Entity\StaticData\Region $region, Array $ids)
	{
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v1.4/summoner/' . join(',', $ids) .  '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getMasteriesBySummonerIds(\AppBundle\Entity\StaticData\Region $region, Array $ids)
	{
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v1.4/summoner/' . join(',', $ids) . '/masteries?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getNamesBySummonerIds(\AppBundle\Entity\StaticData\Region $region, Array $ids)
	{
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v1.4/summoner/' . join(',', $ids) . '/name?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getRunesBySummonerIds(\AppBundle\Entity\StaticData\Region $region, Array $ids)
	{
		$url = HTTPS . $region->getSlug(). '.api.pvp.net/api/lol/' . $region->getSlug(). '/v1.4/summoner/' . join(',', $ids) . '/runes?api_key=' . $this->api_key;
		return $this->request($url);
	}
}
