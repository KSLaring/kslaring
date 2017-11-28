/**
 * Waiting List - Manual submethod Javascript
 *
 * @package         enrol/waitinglist
 * @subpackage      yui
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.course_search = [];

M.core_user.get_course_search = function (name) {
    return this.course_search[name] || null;
};

M.core_user.init_cosearch = function (Y,name,lastsearch) {
    var cosearch = {
        /** This id/name used for this control in the HTML. */
        name : name,
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /** The input element that contains the search term. */
        searchfield : Y.one('#id_search' ),

        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},
        /** The last string that we searched for, so we can avoid unnecessary repeat searches. */
        lastsearch : lastsearch,
        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,


        
        /**
         * Initialises the user manager object
         * @constructor
         */
        init : function() {
            // Hook up the event handler for when the search text changes.
            this.searchfield.on('keyup', this.handle_keyup, this);

        },

        /**
         * Key up hander for the search text box.
         * @param {Y.Event} e the keyup event.
         */
        handle_keyup : function(e) {
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(true)}, this);

            // If enter was pressed, prevent a form submission from happening.
            if (e.keyCode == 13) {
                e.halt();
            }
        },


        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            var value   = this.get_search_text();
            this.searchfield.set('class', '');
            if (this.lastsearch == value && !forceresearch) {
                return;
            }

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });


            var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/course_report/coursesearch.php', {
                method: 'POST',
                data: 'search=' + value + '&sesskey=' +M.cfg.sesskey,
                on: {
                    complete: this.handle_response
                },
                context:this
            });
            this.iotransactions[iotrans.id] = iotrans;

            this.lastsearch = value;
        },

        /**
         * Handle what happens when we get some data back from the search.
         * @param {int} requestid not used.
         * @param {object} response the list of users that was returned.
         */
        handle_response : function(requestid, response) {
            try {
                delete this.iotransactions[requestid];
                if (!Y.Object.isEmpty(this.iotransactions)) {
                    // More searches pending. Wait until they are all done.
                    return;
                }
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.searchfield.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            }catch (e) {
                this.searchfield.addClass('error');
                return new M.core.exception(e);
            }
        },

        /**
         * This method should do the same sort of thing as the PHP method
         * user_selector_base::output_options.
         * @param {object} data the list of users to populate the list box with.
         */
        output_options : function(data) {
            // Clear out the existing options, keeping any ones that are already selected.
            var datacourses;
            var courses;
            var indexco;
            var infoco;
            var index;
            var selected = {};

            Y.one("#id_course_list").all('option').each(function(option){
                if (option.get('selected') && option.get('value') != 0) {
                    selected[option.get('value')] = option.get('value');
                }
                option.remove();
            });

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {
                // Get data
                datacourses = data.results[index];

                // New courses
                courses = datacourses.courses;
                for (indexco in courses) {
                    infoco = courses[indexco];

                    var option = Y.Node.create('<option value="' + infoco.id + '">' + infoco.name + '</option>');

                    Y.one("#id_course_list").append(option);
                }//for_companies
            }//for_level
        },


        /**
         * Replace
         * @param {string} str
         * @param {string} search The search term
         * @return string
         */
        insert_search_into_str : function(str, search) {
            return str.replace("%%SEARCHTERM%%", search);
        },

        /**
         * Cancel the search delay timeout, if there is one.
         */
        cancel_timeout : function() {
            if (this.timeoutid) {
                clearTimeout(this.timeoutid);
                this.timeoutid = null;
            }
        },
        /**
         * Gets the search text
         * @return String the value to search for, with leading and trailing whitespace trimmed.
         */
        get_search_text : function() {
            return this.searchfield.get('value').toString().replace(/^ +| +$/, '');
        },
        /**
         * Returns true if the selection is empty (nothing is selected)
         * @return Boolean check all the options and return whether any are selected.
         */
        is_selection_empty : function() {
            var selection = false;
            this.listbox.all('option').each(function(){
                if (this.get('selected')) {
                    this.setAttribute('selected','selected');
                    selection = true;
                }
            });
            return !(selection);
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(cosearch, Y.EventTarget, null, null, {});
    // Initialise the user selector
    cosearch.init();
    // Store the user selector so that it can be retrieved
    this.course_search[name] = cosearch;

    window.onbeforeunload = null;

    // Return the user selector
    return cosearch;
};
