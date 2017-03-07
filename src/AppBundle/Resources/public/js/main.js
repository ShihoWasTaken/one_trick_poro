$(document).ready(function () {
    $('.region-div').click(function () {
        var text = $(this).children().first().text();
        $('#button-region-text').text(text.toUpperCase());
        $('#searchbar-region').attr('value', text);
        $('#modalChooseRegion').modal('hide');
        Cookies.set("favorite_region", text, {expires: 365});
    });
});