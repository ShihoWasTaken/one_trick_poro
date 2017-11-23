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

    function sendResolutionStats(windowWidth, windowHeight)
    {
        Cookies.set('screen_resolution', windowWidth + 'x' + windowHeight);
        console.log(windowWidth + 'x' + windowHeight);
    }

    function sendCountryStats()
    {
        $.getJSON("http://jsonip.com/?callback=?", function (data) {
            console.log(data);
            var ip = data.ip
            console.log(data.ip);

            $.getJSON("http://freegeoip.net/json/", function (data) {
                var countryName = data.country_name;
                var countryNCode = data.country_code;
                var ip = data.ip;

                Cookies.set('country', country.country_name);
                console.log(country);

            });

        });
    }

    function sendOSAndBrowserStats()
    {
        Cookies.set('browserAndOS', platform.description);
        console.log(platform.name); // 'IE'
        console.log(platform.version); // '10.0'
        console.log(platform.os.family); // 'Windows Server 2008 R2 / 7 x64'
        console.log(platform.description); // 'IE 10.0 x86 (platform preview; running in IE 7 mode) on Windows Server 2008 R2 / 7 x64'
    }

    var windowWidth = window.screen.width;
    var windowHeight = window.screen.height;

    if(Cookies.get('screen_resolution') === undefined)
    {
        sendResolutionStats(windowWidth, windowHeight);
    }
    else
    {
        if(Cookies.get('screen_resolution') !== windowWidth + 'x' + windowHeight)
        {
            sendResolutionStats(windowWidth, windowHeight);
        }
    }

    if(Cookies.get('country') === undefined)
    {
        sendCountryStats();
    }

    if(Cookies.get('browserAndOS') === undefined)
    {
        sendOSAndBrowserStats();
    }

});