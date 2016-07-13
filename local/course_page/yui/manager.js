/**
 * Course Home Page - Manager Option - Java Script
 *
 * @package         local
 * @subpackage      course_page/YUI
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    05/11/2015
 * @author          eFaktor     (fbv)
 */

// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a manager selector for against the cure_user namespace
M.core_user.manager = null;

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_manager = function () {
    return this.manager|| null;
};

M.core_user.init_manager = function (Y,name,lastsearch,course) {

    var manager_selector = {
        /** This id/name used for this control in the HTML. */
        name : name,
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /** The input element that contains the search term. */
        searchfield : Y.one('#id_' + name + '_search'),

        /** The select element that contains the list of users. */
        listbox : Y.one('#id_' + name),
        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},
        /** The last string that we searched for, so we can avoid unnecessary repeat searches. */
        lastsearch : lastsearch,

        /* Course   */
        course_id : course,

        /**
         * Initialises the user selector object
         * @constructor
         */
        init : function() {
            // Hook up the event handler for when the search text changes.
            this.searchfield.on('keyup', this.handle_keyup, this);

            //this.send_query(true);
        },

        handle_keyup : function(e) {
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false)}, this);

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

            var iotrans = Y.io(M.cfg.wwwroot + '/local/course_page/search.php', {
                            method: 'POST',
                            data: 'search' + '=' + value + '&course=' + this.course_id + '&sesskey=' + M.cfg.sesskey,
                            on: {
                                complete: this.handle_response
                            },
                            context:this
            });
            this.iotransactions[iotrans.id] = iotrans;

            this.lastsearch = value;
            this.listbox.setStyle('background','url(' + M.util.image_url('i/loading', 'moodle') + ') no-repeat center center');
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
                this.listbox.setStyle('background','');
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.searchfield.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.listbox.setStyle('background','');
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
            var selected = [];

            // Clear out the existing options, keeping any ones that are already selected.
            this.listbox.get('options').each(function(){
                this.remove();
            });

            /* Clean Manager List  */
            this.listbox.all('option').each(function(option){
                if (option.get('selected')) {
                    selected[option.get('value')] = option.get('value');
                }
                option.remove();
            });


            // Output each optgroup.
            for (var key in data.results) {
                var result  = data.results[key];


                var managers = result.managers;
                var assigned = result.selected;

                for (var index in managers) {
                    var out = managers[index];

                    var option = Y.Node.create('<option value="' + out.id + '">' + out.name + '</option>');
                    this.listbox.append(option);
                }

                /* Mark selected    */
                this.listbox.all('option').each(function(option){
                    if (selected[option.get('value')]) {
                        option.setAttribute('selected','selected');
                    }else {
                        if (option.get('value') == assigned) {
                            option.setAttribute('selected','selected');
                        }else {
                            option.removeAttribute('selected');
                        }//if_assigned
                    }
                });
            }
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
        }

    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(manager_selector, Y.EventTarget, null, null, {});
    // Initialise the user selector
    manager_selector.init();
    // Store the user selector so that it can be retrieved
    this.manager= manager_selector;

    window.onbeforeunload = null;

    // Return the user selector
    return manager_selector;
};