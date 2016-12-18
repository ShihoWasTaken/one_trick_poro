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
	private $region = 'euw';

	public function toSafeLowerCase($string)
	{
		return mb_strtolower($string, 'UTF-8');
	}



	/* Champion v1.2
     * Only 1 entry
     */

	public function getChampions($freeToPlay = false)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($freeToPlay !== false)
		{
			$optional_parameters[] = 'freeToPlay=true';
		}

		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region . CHAMPION_API_VERSION . '/champion?'  . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getChampionById($championId)
	{
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region . CHAMPION_API_VERSION . '/champion/'. $championId . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Champion Mastery
     * Only 1 entry
     */

	private function getPlatformIdByRegion($region)
	{
		return 'EUW1';
	}

	public function getChampionMasteryByChampionId($summonerId, $championId)
	{
		$region = $this->getPlatformIdByRegion($this->region);
		$url = HTTPS . $this->region. '.api.pvp.net/championmastery/location/' . $region . '/player/' . $summonerId . '/champion/' . $championId . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getChampionsMastery($summonerId)
	{
		$region = $this->getPlatformIdByRegion($this->region);
		$url = HTTPS . $this->region. '.api.pvp.net/championmastery/location/' . $region . '/player/' . $summonerId . '/champions' . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getTotalMasteryScore($summonerId)
	{
		$region = $this->getPlatformIdByRegion($this->region);
		$url = HTTPS . $this->region. '.api.pvp.net/championmastery/location/' . $region . '/player/' . $summonerId . '/score' . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getMasteryTopChampions($summonerId, $count = 3)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($count != 3)
		{
			$optional_parameters[] = 'count=' . $count;
		}
		$region = $this->getPlatformIdByRegion($this->region);
		$url = HTTPS . $this->region . '.api.pvp.net/championmastery/location/' . $region . '/player/' . $summonerId . '/topchampions?' . implode('&',$optional_parameters);
		return $this->request($url);
	}


	/* Current Game v1.0
     * Only 1 entry
     */

	public function getCurrentGame($sumonnerId)
	{
		$region = $this->getPlatformIdByRegion($this->region);
		$url = HTTPS . $this->region . '.api.pvp.net/observer-mode/rest/consumer/getSpectatorGameInfo/' . $region . '/' . $sumonnerId . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Featured Games v1.0
     * Only 1 entry
     */

	public function getFeaturedGames()
	{
		$url = HTTPS . $this->region . '.api.pvp.net/observer-mode/rest/featured' . '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Game v1.3
     * Only 1 entry
     */

	public function getRecentGames($summonerId)
	{
		$url = HTTPS . $this->region . '.api.pvp.net/api/lol/' . $this->region . GAME_API_VERSION . '/game/by-summoner/' . $summonerId . '/recent?api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* League v2.5
     * Max 10 entries
     */

	public function getLeaguesBySumonnerIds(Array $summonersIds)
	{
		$url = HTTPS . $this->region . '.api.pvp.net/api/lol/' . $this->region . LEAGUE_API_VERSION . '/league/by-summoner/' . join(',', $summonersIds) .'?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getLeaguesBySumonnerIdsEntry(Array $summonersIds)
	{
		$url = HTTPS . $this->region . '.api.pvp.net/api/lol/' . $this->region . LEAGUE_API_VERSION . '/league/by-summoner/' . join(',', $summonersIds) .'/entry?api_key=' . $this->api_key;
		return $this->request($url);
	}
	
	private function getLeagueChallenger($queue)
	{
		$url = HTTPS . $this->region . '.api.pvp.net/api/lol/' . $this->region . LEAGUE_API_VERSION . '/league/challenger?type=' . $queue .'&api_key='. $this->api_key;
		return $this->request($url);
	}

	public function getLeagueChallengerSoloQueue()
	{
		getLeagueChallenger('RANKED_SOLO_5x5');
	}

	public function getLeagueChallengerRanked5v5()
	{
		getLeagueChallenger('RANKED_TEAM_5x5');
	}

	public function getLeagueChallengerRanked3v3()
	{
		getLeagueChallenger('RANKED_TEAM_3x3');
	}

	private function getLeagueMaster($queue)
	{
		$url = HTTPS . $this->region . '.api.pvp.net/api/lol/' . $this->region . LEAGUE_API_VERSION . '/league/master?type=' . $queue .'&api_key='. $this->api_key;
		return $this->request($url);
	}

	public function getLeagueMasterSoloQueue()
	{
		getLeagueMaster('RANKED_SOLO_5x5');
	}

	public function getLeagueMasterRanked5v5()
	{
		getLeagueMaster('RANKED_TEAM_5x5');
	}

	public function getLeagueMasterRanked3v3()
	{
		getLeagueMaster('RANKED_TEAM_3x3');
	}


	/* Lol Static Data v1.2
     * No rate limit
     */

	public function getStaticDataChampions($locale = null, $version = null, $dataById = null, $champData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/champion?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticDataChampionById($championId, $locale = null, $version = null, $champData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/champion/' . $championId . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticDataItems($locale = null, $version = null, $dataById = null, $itemListData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/item?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticDataItemById($itemId, $locale = null, $version = null, $itemListData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/item/' . $itemId . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticLanguageStrings($locale = null, $version = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/language-strings?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticLanguages()
	{
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/languages?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getStaticMap($locale = null, $version = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/map?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticMasteries($locale = null, $version = null, $masteryListData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/mastery?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticMasteryById($id, $locale = null, $version = null, $masteryListData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/mastery/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticRealm()
	{
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/realm?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getStaticRunes($locale = null, $version = null, $runeListData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/rune?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticRuneById($id, $locale = null, $version = null, $runeListData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/rune/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticSummonerSpells($locale = null, $version = null, $spellData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/summoner-spell?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	public function getStaticSummonerSpellById($id, $locale = null, $version = null, $spellData = null)
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
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/summoner-spell/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}


	public function getStaticDataVersions()
	{
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $this->region . '/v1.2/versions?api_key=' . $this->api_key;
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

	public function getShardByRegion($region)
	{
		$url = 'http://status.leagueoflegends.com/shards/' . $region;
		return $this->request($url);
	}

	/* Match v2.2
     * Only 1 entry
     */

	public function getMatch($id, $includeTimeline = false)
	{
		$optional_parameters = array();
		$optional_parameters[] = 'api_key=' . $this->api_key;

		if($includeTimeline !== false)
		{
			$optional_parameters[] = 'includeTimeline=true';
		}
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v2.2/match/' . $id . '?' . implode('&', $optional_parameters);
		return $this->request($url);
	}

	/* Matchlist v2.2
     * Only 1 entry
     */

	public function getMatchlist($id, $championId = null, $rankedQueues = null, $seasons = null, $beginTime = null, $endTime = null, $beginIndex = null, $endIndex = null)
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

		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v2.2/matchlist/by-summoner/' . $id . '?' . implode('&', $optional_parameters);
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

	public function getRankedStatsBySummonerId($id, $season = null)
	{
		if(isset($season))
		{
			$season = $this->getSeasonCode($season);
		}
		else
		{
			$season = $this->getSeasonCode();
		}
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v1.3/stats/by-summoner/' . $id.  '?season=' . $season . '&api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getSummaryStatsBySummonerId($id, $season = null)
	{
		if(isset($season))
		{
			$season = $this->getSeasonCode($season);
		}
		else
		{
			$season = $this->getSeasonCode();
		}
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v1.3/stats/by-summoner/' . $id.  '/summary?season=' . $season . '&api_key=' . $this->api_key;
		return $this->request($url);
	}

	/* Summoner v1.4
     * Array of max 40 entries
     */
	public function getSummonerByNames(Array $names)
	{
		foreach($names as $name)
		{
			$name = strtolower($name);
			$name = str_replace(' ', '', $name);
		}
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v1.4/summoner/by-name/' . join(',', $names) .  '?api_key=' . $this->api_key;
		$url = str_replace(' ', '', $url);
		return $this->request($url);
	}

	public function getSummonerByIds(Array $ids)
	{
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v1.4/summoner/' . join(',', $ids) .  '?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getMasteriesBySummonerIds(Array $ids)
	{
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v1.4/summoner/' . join(',', $ids) . '/masteries?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getNamesBySummonerIds(Array $ids)
	{
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v1.4/summoner/' . join(',', $ids) . '/name?api_key=' . $this->api_key;
		return $this->request($url);
	}

	public function getRunesBySummonerIds(Array $ids)
	{
		$url = HTTPS . $this->region. '.api.pvp.net/api/lol/' . $this->region. '/v1.4/summoner/' . join(',', $ids) . '/runes?api_key=' . $this->api_key;
		return $this->request($url);
	}
}
