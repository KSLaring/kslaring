/*global define: false, M: true, console: false */
define(['jquery', 'core/notification', 'core/log', 'core/ajax', 'core/templates', 'theme_bootstrapbase/bootstrap'],
    function ($, notification, log, ajax, templates) {
        "use strict";

        log.debug('AMD module categoryselect loaded.');

        var $categorySelectForm = $('#category-select-form'),
            $categorySelectSelect = $('#category-select-select'),
            $categorySelectList = $('#category-select-list-container');

        var handleCategorySelectFormClick = function (e) {
            e.preventDefault();
            var $target = $(e.target),
                $parentli = $target.closest('li'),
                $option = null;

            if ($target.hasClass('catname')) {
                $option = $categorySelectSelect.find('option');
                $option.val($parentli.data('catid'));
                $option.text($target.text());
            } else if ($target.hasClass('tree-icon')) {
                // If sub categories are not loaded, then load and show.
                if ($parentli.hasClass('not-loaded')) {
                    $.when(
                        ajax.call([
                            {
                                methodname: 'local_friadmin_get_subcategories',
                                args: {
                                    catid: $parentli.data('catid')
                                }
                            }
                        ])[0]
                    ).then(function (response) {
                        var html = JSON.parse(response.subcategorieshtml);
                        // console.log(response, html);

                        if (html !== "") {
                            // $parentli = $target.closest('li');
                            var $content = $parentli.find('.content');
                            $content.html(html);
                            $content.addClass('in');
                            $parentli.siblings('li')
                                .not('.collapsed')
                                .addClass('collapsed')
                                .find('.in')
                                .removeClass('in');
                            $parentli
                                .removeClass('collapsed not-loaded')
                                .find('.collapse')
                                .addClass('in');
                        }
                    });
                } else {
                    if ($parentli.hasClass('collapsed')) {
                        // $parentli = $target.closest('li');
                        $parentli.siblings('li')
                            .not('.collapsed')
                            .addClass('collapsed')
                            .find('.in')
                            .removeClass('in');
                        $parentli
                            .removeClass('collapsed')
                            .find('.collapse')
                            .eq(0)
                            .addClass('in');
                    } else {
                        $parentli
                            .addClass('collapsed')
                            .find('.collapse')
                            .removeClass('in');
                        $parentli
                            .find('li')
                            .addClass('collapsed');
                    }
                }
            }
        };

        var handleCategorySelectSelection = function (e) {
            e.preventDefault();

            $categorySelectList.collapse('show');
        };

        return {
            init: function () {
                log.debug('AMD module categoryselect init.');

                $categorySelectForm.on('click', '.catname, .tree-icon.with-children', handleCategorySelectFormClick);
                $categorySelectSelect.on('click select', handleCategorySelectSelection);
            }
        };
    }
);
