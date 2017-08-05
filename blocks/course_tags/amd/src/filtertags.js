/*global define: false */
define(['jquery', 'core/log', 'theme_bootstrapbase/bootstrap'], function ($, log) {
    "use strict";

    log.debug('AMD block_course_tags/filtertags loaded');

    var $collapse = null,
        $toggle = null,
        $tagfilterform = null,
        $tagfilter = null,
        $taggroups = null,
        $showalsoselectedcheckbox = null;

    /**
     * Show only the selected tags in the groups.
     *
     * @param {array} $inplaceeditable The jQuery collection of nodes representing the tags
     */
    var showOnlySelectedTags = function ($inplaceeditable) {
        $inplaceeditable = $inplaceeditable || $taggroups.find('.inplaceeditable');

        hideAllTags($inplaceeditable);
        unhideSelectedTags($inplaceeditable);
        openCloseGroupsDependingOnTagsToShow();
    };

    /**
     * Show all tags in a group.
     *
     * @param {array} $inplaceeditable The jQuery collection of nodes representing the tags
     */
    var showAllTags = function ($inplaceeditable) {
        $inplaceeditable = $inplaceeditable || $taggroups.find('.inplaceeditable');

        $inplaceeditable.parent('li').removeClass('hidden');
    };

    /**
     * Hide all tags in a group.
     *
     * @param {array} $inplaceeditable The jQuery collection of nodes representing the tags
     */
    var hideAllTags = function ($inplaceeditable) {
        $inplaceeditable = $inplaceeditable || $taggroups.find('.inplaceeditable');

        $inplaceeditable.parent('li').addClass('hidden');
    };

    /**
     * Show all selected tags in a group.
     *
     * @param {array} $inplaceeditable The jQuery collection of nodes representing the tags
     */
    var unhideSelectedTags = function ($inplaceeditable) {
        $inplaceeditable = $inplaceeditable || $taggroups.find('.inplaceeditable');

        $inplaceeditable.each(function () {
            var $ele = $(this);
            if ($ele.data('value') === 1) {
                $ele.parent('li').removeClass('hidden');
            }
        });
    };

    /**
     * Check if a group contains selected tags.
     *
     * @param {array} $inplaceeditable The jQuery collection of nodes representing the tags
     * @returns {boolean} The result
     */
    var hasSelectedTags = function ($inplaceeditable) {
        var result = false;

        if ($inplaceeditable.filter('[data-value="1"]').length) {
            result = true;
        }

        return result;
    };

    /**
     * Open or close accordions depending on the condition if the accordion contains not hidden tags.
     */
    var openCloseGroupsDependingOnTagsToShow = function () {
        // Open all groups with found tags, hide those without.
        $taggroups
            .find('.accordion-body')
            .each(function () {
                var $ele = $(this);
                if ($ele.has('li:not(.hidden)').length) {
                    $ele.collapse('show');
                } else {
                    $ele.collapse('hide');
                }
            });
    };

    /**
     * Hide the accordion.
     * Return a promise which is resolved when the accordion 'hidden' event is fired.
     * Trigger accordion hide.
     *
     * @param {array} $accordionbody The jQuery node for the accordion body
     */
    var accordionHide = function ($accordionbody) {
        var dfd = $.Deferred(function (d) {
            $accordionbody.one('hidden.bs.collapse', function () {
                d.resolve();
            });
        });

        $accordionbody.collapse('hide');

        return dfd.promise();
    };

    /**
     * Prevent the default form behaviour.
     *
     * @param {object} e The jQuery event object
     */
    var handleTagFilterForm = function (e) {
        e.preventDefault();
    };

    /**
     * React on
     * _ the text entered in the filter field
     * _ a change of the »Show selectd tags« checkbox
     *
     * Show all tags which have the entered text as part of their name.
     * Show also the selected tags when the checkbox is selected.
     */
    var handleTagFiltering = function () {
        var $input = null,
            filter = '',
            $tagLists = null,
            $tags = null;

        $input = $tagfilter;
        filter = $input.val().toLowerCase();

        if (filter !== '') {
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
                unhideSelectedTags($taggroups.find('.inplaceeditable'));
            }

            openCloseGroupsDependingOnTagsToShow();
        } else {
            if ($showalsoselectedcheckbox.is(":checked")) {
                showOnlySelectedTags();
            } else {
                $.when(accordionHide($taggroups.find('.accordion-body')))
                    .done(function () {
                        hideAllTags($taggroups.find('.inplaceeditable'));
                    });
            }
        }
    };

    /**
     * Catch the event and prevent the Bootstrap accordion handling.
     *
     * Before the open/close action is triggered deal with the »Show selected tags« state to
     * either always show the selected tags and only show/hide the other tags or show/hide all tags.
     * If the selected tags shall be shown close the arccordion and open it again with the changed tag set
     * to avoid the accordion content to jump when tags are shown/hidden.
     *
     * @param {object} e The jQuery event object
     */
    var handleAccordionToggle = function (e) {
        var $toggle = $(e.target),
            $accordionbody = $toggle.parents('.accordion-group').find($toggle.attr('href')),
            $inplaceeditable = $accordionbody.find('.inplaceeditable');

        e.stopPropagation();
        e.preventDefault();

        // Check the accordion open/close state.
        if ($accordionbody.hasClass('in')) {
            // Accordion is open.
            if ($accordionbody.has('li.hidden').length) {
                // If the accordion contains hidden tags, just show all tags. (close, unhide, open)
                $.when(accordionHide($accordionbody))
                    .done(function () {
                        showAllTags($inplaceeditable);
                        $accordionbody.collapse('show');
                    });
            } else {
                // If no tags are hidden, then hide all except the selected.
                if ($showalsoselectedcheckbox.is(":checked") &&
                    hasSelectedTags($accordionbody.find('.inplaceeditable'))) {
                    // If there are selected tags show only those. (close, hide, open)
                    $.when(accordionHide($accordionbody))
                        .done(function () {
                            hideAllTags($inplaceeditable);
                            unhideSelectedTags($inplaceeditable);
                            $accordionbody.collapse('show');
                        });
                } else {
                    // If there are no selecteed tags just close the accodion and hide all tags.
                    $.when(accordionHide($accordionbody))
                        .done(function () {
                            hideAllTags($inplaceeditable);
                        });
                }
            }
        } else {
            // Accordion is closed.
            showAllTags($inplaceeditable);
            $accordionbody.collapse('show');
        }
    };

    /**
     * Catch the Bootstrap collapse events for additional action.
     *
     * When the showalsoselected-checkbox is checked then show/hide the tags but always show the selected tags.
     *
     * @param {object} e The jQuery event object
     */
    var handlePreCollapseEvents = function (e) {
        var $ele = $(e.target);
        // console.log('handlePreCollapseEvents', e.type);

        if (e.type === 'show') {

        } else if (e.type === 'hide') {

        }
    };

    /**
     * Catch the Bootstrap collapse events for additional action.
     *
     * When the showalsoselected-checkbox is checked then show/hide the tags but always show the selected tags.
     *
     * @param {object} e The jQuery event object
     */
    var handlePostCollapseEvents = function (e) {
        var $ele = $(e.target);
        // console.log('handlePreCollapseEvents', e.type);

        if (e.type === 'shown') {

        } else if (e.type === 'hidden') {

        }
    };

    /**
     * Create an attribute mutation observer for the given jQuery element/s.
     * Log the target node to the console when the style attribute has changed.
     *
     * @param {array} $ele The jQuery node/s to observe
     */
    var initMutationObserver = function ($ele) {
        // select the target node
        $ele.each(function () {
            var target = $(this).get(0);
            console.log('initMutationObserver', target);

            // Create an observer instance.
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName === 'style') {
                        console.log($(mutation.target).clone());
                    }
                });
            });

            // Configuration of the observer.
            var config = {attributes: true, childList: false, characterData: false};

            // Pass in the target node, as well as the observer options.
            observer.observe(target, config);
        });
    };

    /**
     * Return the initialization function init, called from PHP by requires->js_call_amd.
     * Set up the element variables for jQuery, set up the event handlers.
     * Call the showOnlySelectedTags function to show the selected tags.
     */
    return {
        init: function () {
            var $inplaceeditable = null;

            log.debug('AMD block_course_tags/filtertags init');

            $tagfilterform = $('#tag-filter-form');
            $tagfilter = $('#tag-filter');
            $taggroups = $('#taggroup-accordion');
            $collapse = $taggroups.find('.collapse');
            $toggle = $taggroups.find('.accordion-toggle');
            $showalsoselectedcheckbox = $('#showalsoselected-checkbox');

            $tagfilterform.on('submit', handleTagFilterForm);
            $tagfilterform.on('keyup', '#tag-filter', handleTagFiltering);
            $showalsoselectedcheckbox.on('change', handleTagFiltering);
            $toggle.on('click', handleAccordionToggle);
            // $collapse.on('show.bs.collapse, hide.bs.collapse', handlePreCollapseEvents);
            // $collapse.on('shown.bs.collapse, hidden.bs.collapse', handlePostCollapseEvents);
            // initMutationObserver($('.accordion-body.collapse'));

            // Initialize the Bootstrap collapse JS and don't toggle during initialization.
            $collapse.collapse({
                toggle: false
            });

            if ($showalsoselectedcheckbox.is(":checked")) {
                showOnlySelectedTags();
            }
        }
    };
});
