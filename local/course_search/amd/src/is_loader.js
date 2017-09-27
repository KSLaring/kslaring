/*global define: false */
define([
    'jquery',
    'local_course_search/js_config',
    'javascript/infinite-scroll.pkgd'
], function ($, cfg, InfiniteScroll) {
    // Return the third party module so we can depend on this loader and use the thirdparty module directly.

    return InfiniteScroll;
});
