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
            }
        }
    });
});
