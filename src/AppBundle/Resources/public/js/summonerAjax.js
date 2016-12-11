$( document ).ready(function() {
    var isChestsAndMasteriesLoaded = false;
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href") // activated tab
        //alert(target);
        switch(target)
        {
            //case "#championMasteries":
            case "#chests":
                if(!isChestsAndMasteriesLoaded)
                {
                    var JSONData;
                    var summonerId = $('#summonerId').text();
                    var region = $('#summonerRegion').text();
                    $.ajax({
                        url: Routing.generate('app_summoner_chests_masteries_ajax', {'region': summonerId, 'summonerId': region }),
                        type: 'POST',
                        data: {'summonerId': summonerId, 'region': region},
                        dataType: 'json',
                        success: function(data) {
                            var JSONData = JSON.parse(data);
                            //$('#chests').html(parsed.chests);
                            //$('#championMasteries').html(parsed.championMasteries);
                        }
                    });
                    isChestsAndMasteriesLoaded = true;
                }
                break;
            default:
                break;
        }
    });
});