/*global define: false, M: true */
define(['jquery', 'core/notification', 'core/log', 'core/ajax', 'core/templates', 'theme_bootstrapbase/bootstrap'],
    function ($, notification, log, ajax, templates) {
        "use strict";

        log.debug('AMD module loaded.');

        var selectedTags = [],
            courses = [],
            $searcharea = null,
            $coursesearchform = null,
            $formsearch = null,
            $tagarea = null,
            $selectedtags = null,
            $resultarea = null,
            hideitemClass = 'hide-item',
            activityContextObj = {group: null, name: null, addtext: null, remove: 0};

        var grepSelectedTags = function (data) {
            var groups = data.tags.groups,
                found;

            if (groups.length) {
                groups.forEach(function (val) {
                    found = $.grep(val.shown, function (item) {
                        return (item.checked === 1);
                    });

                    if (found.length) {
                        selectedTags = $.merge(selectedTags, found);
                    }
                });

                renderSelectedTags({selectedTags: selectedTags});
            }
        };

        var renderSelectedTags = function (context) {
            templates
                .render('local_playground/course_search_selected_tags', context)
                .done(function (html) {
                    $tagarea.append(html);
                    $selectedtags = $tagarea.find('.selected-tags');
                });
        };

        var renderTags = function (context) {
            templates
                .render('local_playground/course_search_region_tags', context)
                .done(function (html) {
                    $formsearch.after(html);
                });
        };

        var renderResults = function (context) {
            templates
                .render('local_playground/course_search_results', context)
                .done(function (html) {
                    $resultarea.append(html);
                });
        };

        var checkboxClickHandler = function () {
            var $ele = $(this),
                checked = $ele.is(":checked"),
                $relatedTag = null,
                activityContext = $.extend({}, activityContextObj, {
                    group: $ele.parent().data('group'),
                    name: $ele.parent().data('name')
                });

            // Remove or add the related tag in the selected tag list depending on the checked status.
            if (!checked) {
                $relatedTag = $tagarea
                    .find('button')
                    .filter('[data-name="' + $ele.parent().data('name') + '"]')
                    .parent('li')
                    .remove();
                activityContext.remove = 1;
            } else {
                templates
                    .render('local_playground/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedtags.append(html);
                    });
            }

            updateCourses();
        };

        var selectedTagClickHandler = function () {
            var $ele = $(this);

            if ($ele.data('group') === 'searchquery') {
                $coursesearchform.find('.search-query').val('');
            } else {
                $searcharea
                    .find('label')
                    .filter('[data-name="' + $ele.data('name') + '"]')
                    .find('input').prop('checked', false);
            }

            $ele.remove();

            updateCourses();
        };

        var handleTextSearch = function (e) {
            var $form = $(this),
                text,
                activityContext;

            // Don't trigger form submit action for the page.
            e.preventDefault();

            text = $form.find('.search-query').val();
            activityContext = $.extend({}, activityContextObj, {group: 'searchquery', name: text, addtext: 'Search text: '});

            // Remove a possibly present search text tag.
            $tagarea.find('li').has('[data-group="searchquery"]').remove();

            // Add the search string as a tag.
            if (text !== '') {
                templates
                    .render('local_playground/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedtags.append(html);
                    });
            }

            updateCourses();
        };

        var getSelectedSearchText = function () {
            return $coursesearchform.find('#course-search').val().toLowerCase();
        };

        var getSelectedTags = function () {
            var checkeditems,
                searchItems = [];

            checkeditems = $coursesearchform.find('label').has(':checked');
            checkeditems.each(function () {
                var $item = $(this);
                searchItems.push($item.data('group') + '-' + $item.data('name'));
            });

            return searchItems;
        };

        /**
         * Walk all course items in the course data object and collect the ids of the matching courses.
         * Check if any of the selected search criteria match, if one matches add the id to the show list.
         *
         * Then walk the course nodes in the display area and set the display status.
         *
         */
        var updateCourses = function () {
            var selectedTags = getSelectedTags(),
                searchText = getSelectedSearchText(),
                textSearch = (searchText !== ''),
                courseIDsToShow = [],
                expectedmatches,
                matchcounter,
                i,
                li,
                j,
                lj;

            // Loop through all courses and collect the course ids of the courses to be shown.
            // Show the courses only when all search criteria match.
            expectedmatches = textSearch ? selectedTags.length + 1 : selectedTags.length;
            for (i = 0, li = courses.length; i < li; i++) {
                matchcounter = 0;
                // Check if the text index contains the search string.
                if (textSearch) {
                    if (courses[i].textcollection.indexOf(searchText) !== -1) {
                        matchcounter++;
                    }
                }

                // Loop through all selected checkboxes.
                for (j = 0, lj = selectedTags.length; j < lj; j++) {
                    // Check if the selected item is in the indexed tag collation.
                    if (courses[i].tagcollection.indexOf(selectedTags[j]) !== -1) {
                        matchcounter++;
                    }
                }

                if (matchcounter === expectedmatches) {
                    courseIDsToShow.push(courses[i].id);
                }
            }

            // If tags are selected but no courses match then set the id list to -1.
            if (!courseIDsToShow.length && selectedTags.length) {
                courseIDsToShow.push(-1);
            }

            showHideCourses(courseIDsToShow);
        };

        /**
         * Set the visibility of the courses in the cards and list view.
         *
         * @param courseIDsToShow The list of course ids to show
         */
        var showHideCourses = function (courseIDsToShow) {
            var $coursecards = $resultarea.find('.course-cards'),
                $courselist = $resultarea.find('.course-list');

            if (courseIDsToShow.length) {
                // If first item is -1 then hide all courses.
                if (courseIDsToShow[0] === -1) {
                    courses.forEach(function (item) {
                        $coursecards.find('#coursecarditem-' + item.id).addClass(hideitemClass);
                        $courselist.find('#courselistitem-' + item.id).addClass(hideitemClass);
                    });
                } else {
                    courses.forEach(function (item) {
                        if (courseIDsToShow.indexOf(item.id) !== -1) {
                            $coursecards.find('#coursecarditem-' + item.id).removeClass(hideitemClass);
                            $courselist.find('#courselistitem-' + item.id).removeClass(hideitemClass);
                        } else {
                            $coursecards.find('#coursecarditem-' + item.id).addClass(hideitemClass);
                            $courselist.find('#courselistitem-' + item.id).addClass(hideitemClass);
                        }
                    });
                }
            } else {
                courses.forEach(function (item) {
                    $coursecards.find('#coursecarditem-' + item.id).removeClass(hideitemClass);
                    $courselist.find('#courselistitem-' + item.id).removeClass(hideitemClass);
                });
            }
        };

        /**
         * Collect all texts from the course title and summary and the tag names into one text string
         * to speed up the text and the tag filter.
         *
         * add
         * {array} tagcollection as [{group}-{name}]
         * {string} textcollection as tagnames + coursename + coursesummary
         *
         * @param {array} data
         * @returns {array} the extended data
         */
        var indexCourseData = function (data) {
            var taglist = [],
                tagcollection = [],
                courseids = [];

            // Collect all text.
            $.each(data, function (i, item) {
                taglist = [];
                tagcollection = [];

                courseids.push(item.id);

                // Collect all tag names.
                $.each(item.tags, function (i, tag) {
                    tagcollection.push(tag.group + '-' + tag.name);
                    taglist.push(tag.name);
                });

                // Collect the tag names and the course name and summary.
                item.tagcollection = tagcollection;
                item.textcollection = taglist.join(',');
                item.textcollection += ' ' + item.name + ' ' + item.summary;
                item.textcollection = item.textcollection.toLowerCase();
            });

            data.courseids = courseids;

            return data;
        };

        return {
            init: function () {
                log.debug('AMD module init.');

                // Get the relevant DOM elements.
                $searcharea = $('#search-area');
                $coursesearchform = $('#course-search-form');
                $tagarea = $('#tag-area');
                $resultarea = $('#result-area');
                $formsearch = $searcharea.find('.form-search');

                // Get the search data.
                $.getJSON("./course_search.json", function (data) {
                    log.debug(data);

                    grepSelectedTags(data);
                    renderTags(data);
                }).fail(notification.exception);

                // Get the course data.
                $.getJSON("./courses.json", function (data) {
                    log.debug(data);

                    courses = indexCourseData(data.courses);
                    data.baseurl = M.cfg.wwwroot;
                    renderResults(data);
                }).fail(notification.exception);

                // Set the event handlers.
                $searcharea.on('click', '[type="checkbox"]', checkboxClickHandler);
                $tagarea.on('click', 'button', selectedTagClickHandler);
                $coursesearchform.on('submit', handleTextSearch);
            }
        };
    }
);
