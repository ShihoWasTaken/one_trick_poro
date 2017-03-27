$('#formLinkSummonerToUser').on('submit', function (e) {
    e.preventDefault();
    $('#formLinkSummonerToUser :submit').attr('disabled', 'disabled');
    $('#loadingDiv').removeClass('hidden');
    var summonerName = $('#summonerToLink').val();
    $.ajax({
        url: Routing.generate('app_link_summoner_to_user'),
        type: 'POST',
        data: {'summonerName': summonerName, 'region': 'euw'},
        dataType: 'html',
        success: function (data) {
            $('#loadingDiv').addClass('hidden');
            $('#formLinkSummonerToUser :submit').removeClass('disabled');
            $('#responseDivLink').html(data);
            $('#formLinkSummonerToUser :submit').removeAttr('disabled');
        }
    });
});

$('#formUnlinkSummonerToUser').on('submit', function (e) {
    e.preventDefault();
    $('#formUnlinkSummonerToUser :submit').attr('disabled', 'disabled');
    var select = document.getElementById("selectSummonersToUnlink");
    var value = select.options[select.selectedIndex].value;
    var valueArray = value.split("|");
    var summonerName = valueArray[1];
    var regionSlug = valueArray[0];
    $.ajax({
        url: Routing.generate('app_unlink_summoner_to_user'),
        type: 'POST',
        data: {'summonerName': summonerName, 'region': regionSlug},
        dataType: 'html',
        success: function (data) {
            $('#formUnlinkSummonerToUser :submit').removeClass('disabled');
            $('#successUnlink').removeClass('hidden');
            $('#formUnlinkSummonerToUser :submit').removeAttr('disabled');
        },
        error: function (data) {
            $('#formUnlinkSummonerToUser :submit').removeClass('disabled');
            $('#errorUnlink').removeClass('hidden');
            $('#formUnlinkSummonerToUser :submit').removeAttr('disabled');
        }
    });
});

$('#modalLinkSummoner').on('shown.bs.modal', function () {
    $("#summonerToLink").focus();
});

var clipboard = new Clipboard('.btn');

clipboard.on('success', function (e) {
    //alert("Le code " + e.text + " a bien été copié dans le presse-papiers");
    console.info('Action:', e.action);
    console.info('Text:', e.text);
    console.info('Trigger:', e.trigger);

    e.clearSelection();
});

clipboard.on('error', function (e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
});
