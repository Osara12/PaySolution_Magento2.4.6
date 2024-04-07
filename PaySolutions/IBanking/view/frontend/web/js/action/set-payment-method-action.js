
define(
    [
        'jquery',
        'mage/url'
    ],
    function ($, url) {
        'use strict';
        return function (banktype) {
            url.setBaseUrl(BASE_URL);
            var link = url.build('paysopayment/jumpapp/request?banktype='+banktype);
            $.mage.redirect(link); //url is your url
        };
    }
);
