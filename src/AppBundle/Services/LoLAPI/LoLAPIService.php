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

class LoLAPIService extends RequestService
{
    private $api_key;

    public function __construct($riot_api_key)
    {
        $this->api_key = $riot_api_key;
    }

    public function toSafeLowerCase($string)
    {
        $string = str_replace(' ', '', $string);
        return mb_strtolower($string, 'UTF-8');
    }

    /* Champion Mastery V3
     * Only 1 entry
     */

    /**
     *
     * ChampionMasteryDTO - This object contains single Champion Mastery information for player and champion combination.
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>chestGranted</td><td>boolean</td><td>Is chest granted for this champion or not in current season.</td></tr>
     * <tr><td>championLevel</td><td>int</td><td>Champion level for specified player and champion combination.</td></tr>
     * <tr><td>championPoints</td><td>int</td><td>Total number of champion points for this player and champion combination - they are used to determine championLevel.</td></tr>
     * <tr><td>championId</td><td>long</td><td>Champion ID for this entry.</td></tr>
     * <tr><td>playerId</td><td>long</td><td>Player ID for this entry.</td></tr>
     * <tr><td>championPointsUntilNextLevel</td><td>long</td><td>Number of points needed to achieve next level. Zero if player reached maximum champion level for this champion.</td></tr>
     * <tr><td>championPointsSinceLastLevel</td><td>long</td><td>Number of points earned since current level has been achieved. Zero if player reached maximum champion level for this champion.</td></tr>
     * <tr><td>lastPlayTime</td><td>long</td><td>Last time this champion was played by this player - in Unix milliseconds time format.</td></tr>
     * </table>
     * @param \AppBundle\Entity\StaticData\Region $region La région du summoner dont on recherche la mastery
     * @param int $summonerId L'ID du summoner dont on recherche la mastery
     * @return ChampionMasteryDTO[] Une liste de ChampionMasteryDTO contenant les informations pour chaque champion
     */
    public function getChampionsMastery(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/champion-mastery/v3/champion-masteries/by-summoner/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /**
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>chestGranted</td><td>boolean</td><td>Is chest granted for this champion or not in current season.</td></tr>
     * <tr><td>championLevel</td><td>int</td><td>Champion level for specified player and champion combination.</td></tr>
     * <tr><td>championPoints</td><td>int</td><td>Total number of champion points for this player and champion combination - they are used to determine championLevel.</td></tr>
     * <tr><td>championId</td><td>long</td><td>Champion ID for this entry.</td></tr>
     * <tr><td>playerId</td><td>long</td><td>Player ID for this entry.</td></tr>
     * <tr><td>championPointsUntilNextLevel</td><td>long</td><td>Number of points needed to achieve next level. Zero if player reached maximum champion level for this champion.</td></tr>
     * <tr><td>championPointsSinceLastLevel</td><td>long</td><td>Number of points earned since current level has been achieved. Zero if player reached maximum champion level for this champion.</td></tr>
     * <tr><td>lastPlayTime</td><td>long</td><td>Last time this champion was played by this player - in Unix milliseconds time format.</td></tr>
     * </table>
     * @param \AppBundle\Entity\StaticData\Region $region La région du summoner dont on recherche la mastery
     * @param int $summonerId L'ID du summoner dont on recherche la mastery
     * @param int $championId L'ID du champion dont on recherche la mastery
     * @return ChampionMasteryDTO Une ChampionMasteryDTO contenant les informations pour chaque champion
     */
    public function getChampionMasteryByChampionId(\AppBundle\Entity\StaticData\Region $region, $summonerId, $championId)
    {
        $url = HTTPS . $region->getHost() . '/lol/champion-mastery/v3/champion-masteries/by-summoner/' . $summonerId . 'by-champion/' . $championId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /**
     * @param \AppBundle\Entity\StaticData\Region $region La région du summoner dont on veut le score total de masteries
     * @param int $summonerId L'ID du summoner dont on veut le score total de masteries
     * @return int Le score total des masteries du summoner
     */
    public function getTotalMasteryScore(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/champion-mastery/v3/scores/by-summoner/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /* Champion V3
     * Only 1 entry
     */

    /**
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>rankedPlayEnabled</td><td>boolean</td><td>Ranked play enabled flag.</td></tr>
     * <tr><td>botEnabled</td><td>boolean</td><td>Bot enabled flag (for custom games).</td></tr>
     * <tr><td>botMmEnabled</td><td>boolean</td><td>Bot Match Made enabled flag (for Co-op vs. AI games).</td></tr>
     * <tr><td>active</td><td>boolean</td><td>Indicates if the champion is active.</td></tr>
     * <tr><td>freeToPlay</td><td>boolean</td><td>Indicates if the champion is free to play. Free to play champions are rotated periodically.</td></tr>
     * <tr><td>id</td><td>long</td><td>Champion ID. For static information correlating to champion IDs, please refer to the LoL Static Data API.</td></tr>
     * </table>
     * @param \AppBundle\Entity\StaticData\Region $region La région où rechercher les informations sur les champions
     * @param bool $freeToPlay Si à true, retourne uniquement les champions gratuits, sinon retourne tout les champions
     * @return ChampionListDto This object contains a collection of champion information.
     */
    public function getChampions(\AppBundle\Entity\StaticData\Region $region, $freeToPlay = false)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($freeToPlay !== false) {
            $optional_parameters[] = 'freeToPlay=true';
        }

        $url = HTTPS . $region->getHost() . '/lol/platform/v3/champions?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    /**
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>rankedPlayEnabled</td><td>boolean</td><td>Ranked play enabled flag.</td></tr>
     * <tr><td>botEnabled</td><td>boolean</td><td>Bot enabled flag (for custom games).</td></tr>
     * <tr><td>botMmEnabled</td><td>boolean</td><td>Bot Match Made enabled flag (for Co-op vs. AI games).</td></tr>
     * <tr><td>active</td><td>boolean</td><td>Indicates if the champion is active.</td></tr>
     * <tr><td>freeToPlay</td><td>boolean</td><td>Indicates if the champion is free to play. Free to play champions are rotated periodically.</td></tr>
     * <tr><td>id</td><td>long</td><td>Champion ID. For static information correlating to champion IDs, please refer to the LoL Static Data API.</td></tr>
     * </table>
     * @param \AppBundle\Entity\StaticData\Region $region La région où rechercher les informations sur les champions
     * @param int $championId L'ID du champion à rechercher
     * @return ChampionDto This object contains champion information.
     */
    public function getChampionById(\AppBundle\Entity\StaticData\Region $region, $championId)
    {
        $url = HTTPS . $region->getHost() . '/lol/platform/v3/champions/' . $championId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /* League V3
     * Max 1 entries
     */

    public function getLeagueOfSumonnerById(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/league/v3/leagues/by-summoner/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getSummonerRanksById(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/league/v3/positions/by-summoner/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    private function getLeagueChallenger(\AppBundle\Entity\StaticData\Region $region, $queue)
    {
        $url = HTTPS . $region->getHost() . '/lol/league/v3/challengerleagues/by-queue/' . $queue . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getLeagueChallengerSoloQueue(\AppBundle\Entity\StaticData\Region $region)
    {
        $this->getLeagueChallenger($region, 'RANKED_SOLO_5x5');
    }

    public function getLeagueChallengerRankedFlex5v5(\AppBundle\Entity\StaticData\Region $region)
    {
        $this->getLeagueChallenger($region, 'RANKED_FLEX_SR');
    }

    public function getLeagueChallengerRankedFlex3v3(\AppBundle\Entity\StaticData\Region $region)
    {
        $this->getLeagueChallenger($region, 'RANKED_FLEX_TT');
    }

    private function getLeagueMaster(\AppBundle\Entity\StaticData\Region $region, $queue)
    {
        $url = HTTPS . $region->getHost() . '/lol/league/v3/masterleagues/by-queue/' . $queue . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getLeagueMasterSoloQueue(\AppBundle\Entity\StaticData\Region $region)
    {
        $this->getLeagueMaster($region, 'RANKED_SOLO_5x5');
    }

    public function getLeagueMasterRankedFlex5v5(\AppBundle\Entity\StaticData\Region $region)
    {
        $this->getLeagueMaster($region, 'RANKED_FLEX_SR');
    }

    public function getLeagueMasterRankedFlex3v3(\AppBundle\Entity\StaticData\Region $region)
    {
        $this->getLeagueMaster($region, 'RANKED_FLEX_TT');
    }

    /* Lol Status V3
     * No rate limit
     */

    public function getShardByRegion(\AppBundle\Entity\StaticData\Region $region)
    {
        $url = HTTPS . $region->getHost() . '/lol/status/v3/shard-data' . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /* Masteries V3
     * Only 1 entry
     */


    /**
     * <b>MasteryPagesDto</b> - This object contains masteries information.
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>pages</td><td>Set[MasteryPageDto]</td><td>Collection of mastery pages associated with the summoner.</td></tr>
     * <tr><td>summonerId</td><td>long</td><td>    Summoner ID.</td></tr>
     * </table>
     * <br>
     * <b>MasteryPageDto</b> - This object contains mastery page information.
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>current</td><td>boolean</td><td>Indicates if the mastery page is the current mastery page.</td></tr>
     * <tr><td>masteries</td><td>List[MasteryDto]</td><td>Collection of masteries associated with the mastery page.</td></tr>
     * <tr><td>name</td><td>string</td><td>Mastery page name.</td></tr>
     * <tr><td>id</td><td>long</td><td>Mastery page ID.</td></tr>
     * </table>
     * <br>
     * <b>MasteryDto</b> - This object contains mastery information.
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>id</td><td>int</td><td>Mastery ID. For static information correlating to masteries, please refer to the LoL Static Data API.</td></tr>
     * <tr><td>rank</td><td>int</td><td>Mastery rank (i.e., the number of points put into this mastery).</td></tr>
     * </table>
     * @param \AppBundle\Entity\StaticData\Region $region La région où rechercher les informations sur le summoner
     * @param $summonerId L'ID du summoner dont on veut chercher les masteries
     * @return MasteryPagesDto
     */
    public function getMasteriesBySummonerId(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/platform/v3/masteries/by-summoner/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }


    /* Match V3
     * Only 1 entry
     */

    public function getMatchById(\AppBundle\Entity\StaticData\Region $region, $matchId)
    {
        $url = HTTPS . $region->getHost() . '/lol/match/v3/matches/' . $matchId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getMatchlist(\AppBundle\Entity\StaticData\Region $region, $summonerId, array $champions = null, array $queues = null, array $seasons = null, $beginTime = null, $endTime = null, $beginIndex = null, $endIndex = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($champions !== null) {
            foreach ($champions as $champion) {
                $optional_parameters[] = 'champion=' . $champion;
            }
        }
        if ($queues !== null) {
            foreach ($queues as $queue) {
                $optional_parameters[] = 'queue=' . $queue;
            }
        }
        if ($seasons !== null) {
            foreach ($seasons as $season) {
                $optional_parameters[] = 'season=' . $season;
            }
        }
        if ($beginTime !== null) {
            $optional_parameters[] = 'beginTime=' . $beginTime;
        }
        if ($endTime !== null) {
            $optional_parameters[] = 'endTime=' . $endTime;
        }
        if ($beginIndex !== null) {
            $optional_parameters[] = 'beginIndex=' . $beginIndex;
        }
        if ($endIndex !== null) {
            $optional_parameters[] = 'endIndex=' . $endIndex;
        }

        $url = HTTPS . $region->getHost() . '/lol/match/v3/matchlists/by-account/' . $summonerId . '?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getRecentGames(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/match/v3/matchlists/by-account/' . $summonerId . '/recent?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getMatchTimeline(\AppBundle\Entity\StaticData\Region $region, $matchId)
    {
        $url = HTTPS . $region->getHost() . '/lol/match/v3/timelines/by-match/' . $matchId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /* Runes V3
     * Only 1 entry
     */
    public function getRunesBySummonerId(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/platform/v3/runes/by-summoner/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /* Spectator V3
     * Only 1 entry
     */

    /**
     * <b>CurrentGameInfo</b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>gameId</td><td>long</td><td>The ID of the game</td></tr>
     * <tr><td>gameStartTime</td><td>long</td><td>The game start time represented in epoch milliseconds</td></tr>
     * <tr><td>platformId</td><td>string</td><td>The ID of the platform on which the game is being played</td></tr>
     * <tr><td>gameMode</td><td>string</td><td>The game mode</td></tr>
     * <tr><td>mapId</td><td>long</td><td>The ID of the map</td></tr>
     * <tr><td>gameType</td><td>string</td><td>The game type</td></tr>
     * <tr><td>bannedChampions</td><td>List[BannedChampion]</td><td>Banned champion information</td></tr>
     * <tr><td>observers</td><td>Observer</td><td>The observer information</td></tr>
     * <tr><td>participants</td><td>List[CurrentGameParticipant]</td><td>The participant information</td></tr>
     * <tr><td>gameLength</td><td>long</td><td>The amount of time in seconds that has passed since the game started</td></tr>
     * <tr><td>gameQueueConfigId</td><td>long</td><td>The queue type (queue types are documented on the Game Constants page)</td></tr>
     * </table>
     * <br>
     * <b>BannedChampion</b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>pickTurn</td><td>int</td><td>The turn during which the champion was banned</td></tr>
     * <tr><td>championId</td><td>long</td><td>The ID of the banned champion</td></tr>
     * <tr><td>teamId</td><td>long</td><td>The ID of the team that banned the champion</td></tr>
     * </table>
     * <br>
     * <b>Observer</b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>encryptionKey</td><td>string</td><td>Key used to decrypt the spectator grid game data for playback</td></tr>
     * </table>
     * <br>
     * <b>CurrentGameParticipant </b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>profileIconId</td><td>long</td><td>The ID of the profile icon used by this participant</td></tr>
     * <tr><td>championId</td><td>long</td><td>The ID of the champion played by this participant</td></tr>
     * <tr><td>summonerName</td><td>string</td><td>The summoner name of this participant</td></tr>
     * <tr><td>runes</td><td>List[Rune]</td><td>The runes used by this participant</td></tr>
     * <tr><td>bot</td><td>boolean</td><td>Flag indicating whether or not this participant is a bot</td></tr>
     * <tr><td>teamId</td><td>long</td><td>The team ID of this participant, indicating the participant's team</td></tr>
     * <tr><td>spell2Id</td><td>long</td><td>The ID of the second summoner spell used by this participant</td></tr>
     * <tr><td>masteries</td><td>List[Mastery]</td><td>The masteries used by this participant</td></tr>
     * <tr><td>spell1Id</td><td>long</td><td>The ID of the first summoner spell used by this participant</td></tr>
     * <tr><td>summonerId</td><td>long</td><td>The summoner ID of this participant</td></tr>
     * </table>
     * <br>
     * <b>Rune</b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>count</td><td>int</td><td>The count of this rune used by the participant</td></tr>
     * <tr><td>runeId</td><td>long</td><td>The ID of the rune</td></tr>
     * </table>
     * <br>
     * <b>Mastery</b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>masteryId</td><td>long</td><td>The ID of the mastery</td></tr>
     * <tr><td>rank</td><td>int</td><td>The number of points put into this mastery by the user</td></tr>
     * </table>
     * @param \AppBundle\Entity\StaticData\Region $region La région où rechercher les informations sur la game en cours
     * @param $summonerId L'ID du summoner dont on veut chercher la game en cours
     * @return CurrentGameInfo
     */
    public function getCurrentGame(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/spectator/v3/active-games/by-summoner/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }


    /**
     * <b>FeaturedGames </b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>clientRefreshInterval</td><td>long</td><td>The suggested interval to wait before requesting FeaturedGames again</td></tr>
     * <tr><td>gameList</td><td>List[FeaturedGameInfo]</td><td>The list of featured games</td></tr>
     * </table>
     * <br>
     * <b>FeaturedGameInfo </b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>gameId</td><td>long</td><td>The ID of the game</td></tr>
     * <tr><td>gameStartTime</td><td>long</td><td>The game start time represented in epoch milliseconds</td></tr>
     * <tr><td>platformId</td><td>string</td><td>The ID of the platform on which the game is being played</td></tr>
     * <tr><td>gameMode</td><td>string</td><td>The game mode</td></tr>
     * <tr><td>mapId</td><td>long</td><td>The ID of the map</td></tr>
     * <tr><td>gameType</td><td>string</td><td>The game type</td></tr>
     * <tr><td>bannedChampions</td><td>List[BannedChampion]</td><td>Banned champion information</td></tr>
     * <tr><td>observers</td><td>Observer</td><td>The observer information</td></tr>
     * <tr><td>participants</td><td>List[CurrentGameParticipant]</td><td>The participant information</td></tr>
     * <tr><td>gameLength</td><td>long</td><td>The amount of time in seconds that has passed since the game started</td></tr>
     * <tr><td>gameQueueConfigId</td><td>long</td><td>The queue type (queue types are documented on the Game Constants page)</td></tr>
     * </table>
     * <br>
     * <b>BannedChampion</b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>pickTurn</td><td>int</td><td>The turn during which the champion was banned</td></tr>
     * <tr><td>championId</td><td>long</td><td>The ID of the banned champion</td></tr>
     * <tr><td>teamId</td><td>long</td><td>The ID of the team that banned the champion</td></tr>
     * </table>
     * <br>
     * <b>Observer</b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>encryptionKey</td><td>string</td><td>Key used to decrypt the spectator grid game data for playback</td></tr>
     * </table>
     * <br>
     * <b>Participant </b>
     * <table border=1>
     * <tr><th>NAME</th><th>DATA TYPE</th><th>DESCRIPTION</th></tr>
     * <tr><td>profileIconId</td><td>long</td><td>The ID of the profile icon used by this participant</td></tr>
     * <tr><td>championId</td><td>long</td><td>The ID of the champion played by this participant</td></tr>
     * <tr><td>summonerName</td><td>string</td><td>The summoner name of this participant</td></tr>
     * <tr><td>bot</td><td>boolean</td><td>Flag indicating whether or not this participant is a bot</td></tr>
     * <tr><td>teamId</td><td>long</td><td>The team ID of this participant, indicating the participant's team</td></tr>
     * <tr><td>spell2Id</td><td>long</td><td>The ID of the second summoner spell used by this participant</td></tr>
     * <tr><td>spell1Id</td><td>long</td><td>The ID of the first summoner spell used by this participant</td></tr>
     * </table>
     * @param \AppBundle\Entity\StaticData\Region $region La région où rechercher les games spectateurs
     * @return FeaturedGames
     */
    public function getFeaturedGames(\AppBundle\Entity\StaticData\Region $region)
    {
        $url = HTTPS . $region->getHost() . '/lol/spectator/v3/featured-games' . '?api_key=' . $this->api_key;
        return $this->request($url);
    }


    /* Summoner V3
     * Only 1 entry
     */
    public function getSummonerByName(\AppBundle\Entity\StaticData\Region $region, $name)
    {
        $name = $this->toSafeLowerCase($name);
        $url = HTTPS . $region->getHost() . '/lol/summoner/v3/summoners/by-name/' . $name . '?api_key=' . $this->api_key;
        //$url = str_replace(' ', '', $url);
        return $this->request($url);
    }

    public function getSummonerByAccountId(\AppBundle\Entity\StaticData\Region $region, $accountId)
    {
        $url = HTTPS . $region->getHost() . '/lol/summoner/v3/summoners/by-account/' . $accountId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getSummonerBySummonerId(\AppBundle\Entity\StaticData\Region $region, $summonerId)
    {
        $url = HTTPS . $region->getHost() . '/lol/summoner/v3/summoners/' . $summonerId . '?api_key=' . $this->api_key;
        return $this->request($url);
    }

    /* Lol Static Data V3
     * No rate limit
     */

    public function getStaticDataChampions(\AppBundle\Entity\StaticData\Region $region, $dataById = true, $locale = null, $version = null, $champListData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;
        $dataByIdStr = ($dataById) ? 'true' : 'false';
        $optional_parameters[] = 'dataById=' . $dataByIdStr;
        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($champListData !== false) {
            $optional_parameters[] = 'champListData=' . $champListData;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/champions?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticDataChampionById(\AppBundle\Entity\StaticData\Region $region, $championId, $locale = null, $version = null, $champData = null)
    {
        $optional_parameters = array();

        $optional_parameters[] = 'api_key=' . $this->api_key;
        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($champData !== false) {
            $optional_parameters[] = 'champData=' . $champData;
        }

        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/champions/' . $championId . '?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticDataItems(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $itemListData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;
        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($itemListData !== false) {
            $optional_parameters[] = 'itemListData=' . $itemListData;
        }

        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/items?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticDataItemById(\AppBundle\Entity\StaticData\Region $region, $itemId, $locale = null, $version = null, $itemData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($itemData !== false) {
            $optional_parameters[] = 'itemData=' . $itemData;
        }

        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/items/' . $itemId . '?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticLanguageStrings(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }

        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/language-strings?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticLanguages(\AppBundle\Entity\StaticData\Region $region)
    {
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/languages?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getStaticMap(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/maps?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticMasteries(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $masteryListData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($masteryListData !== false) {
            $optional_parameters[] = 'masteryListData=' . $masteryListData;
        }

        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/masteries?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticMasteryById(\AppBundle\Entity\StaticData\Region $region, $masteryId, $locale = null, $version = null, $masteryData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($masteryData !== false) {
            $optional_parameters[] = 'masteryData=' . $masteryData;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/masteries/' . $masteryId . '?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticProfileIcon(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/profile-icons?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticRealm(\AppBundle\Entity\StaticData\Region $region)
    {
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/realms?api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getStaticRunes(\AppBundle\Entity\StaticData\Region $region, $locale = null, $version = null, $runeListData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($runeListData !== false) {
            $optional_parameters[] = 'runeListData=' . $runeListData;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/runes?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticRuneById(\AppBundle\Entity\StaticData\Region $region, $runeId, $locale = null, $version = null, $runeData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($runeData !== false) {
            $optional_parameters[] = 'runeDate=' . $runeData;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/runes/' . $runeId . '?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticSummonerSpells(\AppBundle\Entity\StaticData\Region $region, $dataById = true, $locale = null, $version = null, $spellListData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        $dataByIdStr = ($dataById) ? 'true' : 'false';
        $optional_parameters[] = 'dataById=' . $dataByIdStr;
        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($spellListData !== false) {
            $optional_parameters[] = 'spellListData=' . $spellListData;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/summoner-spells?' . implode('&', $optional_parameters);
        return $this->request($url);
    }

    public function getStaticSummonerSpellById(\AppBundle\Entity\StaticData\Region $region, $summonerSpellId, $locale = null, $version = null, $spellData = null)
    {
        $optional_parameters = array();
        $optional_parameters[] = 'api_key=' . $this->api_key;

        if ($locale !== false) {
            $optional_parameters[] = 'locale=' . $locale;
        }
        if ($version !== false) {
            $optional_parameters[] = 'version=' . $version;
        }
        if ($spellData !== false) {
            $optional_parameters[] = 'spellData=' . $spellData;
        }
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/summoner-spells/' . $summonerSpellId . '?' . implode('&', $optional_parameters);
        return $this->request($url);
    }


    public function getStaticDataVersions(\AppBundle\Entity\StaticData\Region $region)
    {
        $url = HTTPS . $region->getHost() . '/lol/static-data/v3/versions?api_key=' . $this->api_key;
        return $this->request($url);
    }

    // TODO: penser à remplacer ça https://discussion.developer.riotgames.com/questions/1167/why-do-you-remove-stats-api.html
    /* Stats v1.3
     * Only 1 entry
     */
    /*

    private function getSeasonCode($season = 7)
    {
        switch ($season) {
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
        if (isset($season)) {
            $season = $this->getSeasonCode($season);
        } else {
            $season = $this->getSeasonCode();
        }
        $url = HTTPS . $region->getTag() . '.api.pvp.net/api/lol/' . $region->getTag() . '/v1.3/stats/by-summoner/' . $id . '/ranked?season=' . $season . '&api_key=' . $this->api_key;
        return $this->request($url);
    }

    public function getSummaryStatsBySummonerId(\AppBundle\Entity\StaticData\Region $region, $id, $season = null)
    {
        if (isset($season)) {
            $season = $this->getSeasonCode($season);
        } else {
            $season = $this->getSeasonCode();
        }
        $url = HTTPS . $region->getTag() . '.api.pvp.net/api/lol/' . $region->getTag() . '/v1.3/stats/by-summoner/' . $id . '/summary?season=' . $season . '&api_key=' . $this->api_key;
        return $this->request($url);
    }
*/

}
