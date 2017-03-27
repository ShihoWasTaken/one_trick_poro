$(document).ready(function () {
    $('.region-div').click(function () {
        var text = $(this).children().first().text();
        $('#button-region-text').text(text.toUpperCase());
        $('#searchbar-region').attr('value', text);
        $('#modalChooseRegion').modal('hide');
        Cookies.set("favorite_region", text, {expires: 365});
    });

    // Bootstrap Tab URL
    $(document).ready(function () {
        if (location.hash !== '') $('a[href="' + location.hash + '"]').tab('show');
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (history.pushState) history.pushState(null, null, '#' + $(e.target).attr('href').substr(1)); else location.hash = '#' + $(e.target).attr('href').substr(1);
        });
    });
});