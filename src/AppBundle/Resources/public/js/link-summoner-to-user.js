var manageSummoner = function(summonerName, route)
{
    var url = Routing.generate(route, { 'summonerName':summonerName });
    return $.post(url);
}

/*
$('#linkSummonerToUser').click(function()
{
    var summonerName = $('#summonerToLink').val();
    $('#loader').removeClass('hidden');
    //alert('Le nom de l\'invocateur est ' + summonerName);
    $(this).addClass('disabled');

    var json = manageSummoner(summonerName, 'app_link_summoner_to_user');
    $('#loader').addClass('hidden');
    alert(json);


});
*/
/*
$('#linkSummonerToUser').click(function()
{
    $('#linkSummonerToUser').addClass('disabled');
    $('#loadingDiv').removeClass('hidden');
    var summonerName = $('#summonerToLink').val();
    var idProjet = $(this).attr('id').split('-')[1];
    $.ajax({
        url: Routing.generate('app_link_summoner_to_user', { 'summonerName':summonerName }),
        type: 'POST',
        data: {'summonerName': summonerName},
        dataType: 'html',
        success: function(data) {
            $('#loadingDiv').addClass('hidden');
            $('#linkSummonerToUser').removeClass('disabled');
            $('#responseDiv').html(data);

            //alert(data.summonerName.id);
        }
    });
});
*/


$('#formLinkSummonerToUser').on('submit', function(e)
{
    $('#formLinkSummonerToUser :submit').attr('disabled', 'disabled');
    $('#loadingDiv').removeClass('hidden');
    var summonerName = $('#summonerToLink').val();
    e.preventDefault();
    $.ajax({
        url: Routing.generate('app_link_summoner_to_user', { 'summonerName':summonerName }),
        type: 'POST',
        data: {'summonerName': summonerName},
        dataType: 'html',
        success: function(data) {
            $('#loadingDiv').addClass('hidden');
            $('#linkSummonerToUser').removeClass('disabled');
            $('#responseDiv').html(data);
            $('#formLinkSummonerToUser :submit').removeAttr('disabled');
        }
    });
});

$('#modalLinkSummoner').on('shown.bs.modal', function() {
    $("#summonerToLink").focus();
});

var clipboard = new Clipboard('.btn');

clipboard.on('success', function(e) {
    //alert("Le code " + e.text + " a bien été copié dans le presse-papiers");
    console.info('Action:', e.action);
    console.info('Text:', e.text);
    console.info('Trigger:', e.trigger);

    e.clearSelection();
});

clipboard.on('error', function(e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
});

/*
$('.remove-friend').click(function()
{
    var friendId = $(this).data('user-id');
    $(this).addClass('disabled');
    manageSummoner(friendId, 'tech_corp_front_user_remove_friend');
    $(this).text('Supprimé');
});
    */