/*global define: false, requirejs: false */
define(['core/url'], function (url) {
    requirejs.config({
        // Extra path and shim config here.
        paths: {
            "javascript": url.relativeUrl('local/course_search/javascript')
        },
        shim: {
            'bootstrap-datepicker.min': {
                deps: ['jquery', 'theme_bootstrapbase/bootstrap'],
                exports: 'bootstrap-datepicker'
            },
            'infinite-scroll.pkgd': {
                deps: ['jquery'],
                // exports: 'InfiniteScroll'
                exports: 'jQuery.fn.infiniteScroll'
            },
            'jquery-bridget/jquery-bridget': {
                deps: ['jquery'],
                exports: 'jQueryBridget'
            },
            'underscore': {
                path: 'javascript/lodash.min',
                exports: '_'
            }
        }
    });
});