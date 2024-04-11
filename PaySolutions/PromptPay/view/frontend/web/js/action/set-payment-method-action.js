
define(
    [
        'jquery',
        'mage/url'
    ],
    function ($, url) {
        'use strict';
        return function () {
            url.setBaseUrl(BASE_URL);
            var link = url.build('paysopayment/jumpapp/request');
            $.mage.redirect(link); //url is your url
        }; 
    }
);
