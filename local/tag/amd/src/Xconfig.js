/*global define: false, requirejs: false */
define(['core/url'], function (url) {
    requirejs.config({
        // Extra path and shim config here.
        paths: {
            "javascript": url.relativeUrl('local/tag/javascript')
        },
        shim: {
            'jquery.ui.touch-punch.min': {
                deps: ['jquery', 'jqueryui'],
                exports: 'jQuery.ui.touch-punch'
            }
        }
    });
});
