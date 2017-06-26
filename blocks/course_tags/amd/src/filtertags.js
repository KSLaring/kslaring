/*global define: false */
define(['jquery', 'core/log', 'theme_bootstrapbase/bootstrap'], function ($, log) {
    "use strict";

    log.debug('AMD block_course_tags/filtertags loaded');

    var $tagfilterform = null,
        $taggroups = null,
        $showalsoselectedcheckbox = null;

    var handleShowalsoselectedCheckbox = function () {
        var $inplaceeditable = $taggroups.find('.inplaceeditable');

        if ($showalsoselectedcheckbox.is(":checked")) {
            $inplaceeditable.each(function () {
                var $ele = $(this);
                if ($ele.data('value') === 1) {
                    $ele.parent('li').removeClass('hidden');
                } else {
                    $ele.parent('li').addClass('hidden');
                }
            });
        } else {
            $inplaceeditable.each(function () {
                $(this).parent('li').removeClass('hidden');
            });
        }

        openclosegroups();
    };

    var handleTagFilterForm = function (e) {
        e.preventDefault();
    };

    var handleTagFilterText = function (e) {
        var $input = null,
            filter = '',
            $tagLists = null,
            $tags = null;

        e.preventDefault();

        $input = $(e.currentTarget);
        filter = $input.val().toLowerCase();
        $tagLists = $taggroups.find('.unlist');
        $tags = $tagLists.find('li');

        $tags.each(function () {
            var $ele = $(this);
            if ($ele.data('tagname').toLowerCase().indexOf(filter) !== -1) {
                $ele.removeClass('hidden');
            } else {
                $ele.addClass('hidden');
            }
        });

        if ($showalsoselectedcheckbox.is(":checked")) {
            $taggroups.find('.inplaceeditable').each(function () {
                var $ele = $(this);
                if ($ele.data('value') === 1) {
                    $ele.parent('li').removeClass('hidden');
                }
            });
        }

        if (filter !== '') {
            openclosegroups();
        } else {
            if ($showalsoselectedcheckbox.is(":checked")) {
                handleShowalsoselectedCheckbox();
            } else {
                $taggroups
                    .find('.accordion-body')
                    .collapse('hide');
            }
        }
    };

    var openclosegroups = function () {
        // Open all groups with found tags, hide those without.
        $taggroups
            .find('.accordion-body')
            .each(function () {
                var ele = $(this);
                if (ele.has('li:not(.hidden)').length) {
                    ele.collapse('show');
                } else {
                    ele.collapse('hide');
                }
            });
    };

    return {
        init: function () {
            log.debug('AMD block_course_tags/filtertags init');

            $tagfilterform = $('#tag-filter-form');
            $taggroups = $('#taggroup-accordion');
            $showalsoselectedcheckbox = $('#showalsoselected-checkbox');

            $tagfilterform.on('submit', handleTagFilterForm);
            $tagfilterform.on('keyup', '#tag-filter', handleTagFilterText);
            $showalsoselectedcheckbox.on('change', handleShowalsoselectedCheckbox);

            handleShowalsoselectedCheckbox();
        }
    };
});
