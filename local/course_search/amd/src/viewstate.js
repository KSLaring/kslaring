/*global define: false, M: true, console: false */
define(['jquery', 'core/notification', 'core/log', 'core/ajax', 'core/templates',
        'local_course_search/cookie',
        'local_course_search/ld_loader'
    ],
    function ($, notification, log, ajax, templates, cookie, _) {
        "use strict";

        log.debug('AMD module viewstate loaded.');

        // Viewstate structure - groups separated by »|«, items separated by »,«.
        // '0 (cards) OR 1 (list)|filtertext(search text)|1,2,3 (selected tag list)|2017-10-12(from),0(to)|name(sortbystate),0(desc),0(showtags)'.
        var cookiename = 'viewstate',
            viewstate = null,
            statearray = null,
            viewstatearray = [[], [], [], [], []],
            viewstatearrayindex = {
                'view': 0,
                'text': 1,
                'tags': 2,
                'date': 3,
                'display': 4
            },
            defaultviewstate = '1|course|1,23,31|2017-10-06,0|date,1,1';

        /**
         * Save the user selections in a cookie.
         */
        var writeViewStateToCookie = function () {
            var cookiestring = '',
                statearray = [];

            viewstatearray.forEach(function (item) {
                statearray.push(item.join(','));
            });

            cookiestring = statearray.join('|');
            cookie.create(cookiename, cookiestring, 14);

            log.debug(cookiestring);
        };

        // Return the public properties and methods.
        return {
            // The view state string.
            viewstate: viewstate,

            // The view state array.
            viewstatearray: viewstatearray,

            /**
             * Initialize the viewstate from the cookie or from the default.
             */
            init: function () {
                log.debug('AMD module viewstate init.');

                viewstate = cookie.read(cookiename);

                if (viewstate === null) {
                    viewstate = defaultviewstate;
                    cookie.create(cookiename, defaultviewstate);
                }

                // Fill the viewstatearray.
                statearray = viewstate.split('|');

                statearray.forEach(function (item, i) {
                    if (item.length) {
                        viewstatearray[i] = item.split(',');
                    } else {
                        viewstatearray[i] = [];
                    }
                });

                log.debug(viewstate);
                log.debug(viewstatearray);
            },

            /**
             * Set the view and save the changed state.
             *
             * @param {int|string} which The view state number "0" = cards, "1" = list
             */
            setView: function (which) {
                viewstatearray[viewstatearrayindex.view][0] = which.toString();
                writeViewStateToCookie();

                $('body').trigger('viewstate:change', ['view']);
            },

            /**
             * Get the view.
             */
            getView: function () {
                return viewstatearray[viewstatearrayindex.view][0];
            },

            /**
             * Set the search text and save the changed state.
             *
             * @param {string} what The search text
             */
            setText: function (what) {
                viewstatearray[viewstatearrayindex.text][0] = what.replace(',', '').toLowerCase();
                writeViewStateToCookie();

                $('body').trigger('viewstate:change', ['text']);
            },

            /**
             * Get the search text.
             */
            getText: function () {
                var text = '';

                // If text is seaved then retrun the text.
                if (viewstatearray[viewstatearrayindex.text].length) {
                    text = viewstatearray[viewstatearrayindex.text][0];
                }

                return text;
            },

            /**
             * Set a tag and save the changed state.
             *
             * Add a tag - action = "add" | undefined
             * Remove a tag - action = "remove"
             *
             * @param {int|string} id The tag id
             * @param {string} action The action - add | remove
             */
            setTag: function (id, action) {
                if (action === "remove") {
                    _.remove(viewstatearray[viewstatearrayindex.tags], function (n) {
                        return n.toString() === id.toString()
                    });
                } else {
                    if (viewstatearray[viewstatearrayindex.tags].indexOf(id.toString()) === -1) {
                        viewstatearray[viewstatearrayindex.tags].push(id.toString());
                    }
                }
                writeViewStateToCookie();

                $('body').trigger('viewstate:change', ['tag']);
            },

            /**
             * Get the search text.
             */
            getTags: function () {
                return viewstatearray[viewstatearrayindex.tags];
            },

            /**
             * Set a date and save the changed state.
             *
             * @param {string} which The date string "2017-10-06" | ""
             * @param {string} type The date type - date-from | date-to
             */
            setDate: function (which, type) {
                if (type === "date-from") {
                    viewstatearray[viewstatearrayindex.date][0] = which;
                } else if (type === "date-to") {
                    viewstatearray[viewstatearrayindex.date][1] = which;
                } else {
                    return;
                }
                writeViewStateToCookie();

                $('body').trigger('viewstate:change', [type]);
            },

            /**
             * Get the search text.
             *
             * @return {array} The dates array [from,to]
             */
            getDates: function () {
                return viewstatearray[viewstatearrayindex.date];
            },

            /**
             * Get the from date.
             *
             * @return {int} The from date
             */
            getFromDate: function () {
                return viewstatearray[viewstatearrayindex.date][0];
            },

            /**
             * Get the to date.
             *
             * @return {int} The to date
             */
            getToDate: function () {
                return viewstatearray[viewstatearrayindex.date][1];
            },

            /**
             * Set a display related parameter and save the changed state.
             *
             * @param {int|string} what The value: "name" | 0 | 1
             * @param {string} type The type: "sort" | "desc" | "showtags"
             */
            setDisplay: function (what, type) {
                if (type === "sort") {
                    viewstatearray[viewstatearrayindex.display][0] = what;
                } else if (type === "desc") {
                    viewstatearray[viewstatearrayindex.display][1] = what;
                } else if (type === "showtags") {
                    viewstatearray[viewstatearrayindex.display][2] = what;
                } else {
                    return;
                }
                writeViewStateToCookie();

                $('body').trigger('viewstate:change', type);
            },

            /**
             * Get the display.
             *
             * @return {array} The display values array [colname,desc,showtags]
             */
            getDisplay: function () {
                return viewstatearray[viewstatearrayindex.display];
            },

            /**
             * Get the sort column name.
             *
             * @return {string} The sort column name
             */
            getDisplaySort: function () {
                return viewstatearray[viewstatearrayindex.display][0];
            },

            /**
             * Get the desc value.
             *
             * @return {string} The desc value 0|1
             */
            getDisplayDesc: function () {
                return viewstatearray[viewstatearrayindex.display][1];
            },

            /**
             * Get the show tags value.
             *
             * @return {string} The show tags value 0|1
             */
            getDisplayShowtags: function () {
                return viewstatearray[viewstatearrayindex.display][2];
            }
        };
    }
);
