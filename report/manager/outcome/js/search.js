/**
 * Report Competence Manager - Java Script - Super Users Selector
 *
 * @package         report
 * @subpackage      manager/super_user/js
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    16/10/2015
 * @author          eFaktor     (fbv)
 */

// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.job_roles_selectors = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_job_role_selector = function (name) {
    return this.job_roles_selectors[name] || null;
};

M.core_user.init_job_role_selector = function (Y, name, hash, outcome_id,lastsearch,remove_jr) {

    var job_role_selector = {
        /** This id/name used for this control in the HTML. */
        name : name,
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /** The input element that contains the search term. */
        searchfield : Y.one('#' + name + '_searchtext'),

        /** The select element that contains the list of users. */
        listbox : Y.one('#id_' + name),
        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},
        /** The last string that we searched for, so we can avoid unnecessary repeat searches. */
        lastsearch : lastsearch,
        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,

        outcomeId : outcome_id,

        /**
         * Initialises the user selector object
         * @constructor
         */
        init : function() {
            // Hook up the event handler for when the search text changes.
            this.searchfield.on('keyup', this.handle_keyup, this);

            // Hook up the event handler for when the selection changes.
            this.listbox.on('keyup', this.handle_selection_change, this);
            this.listbox.on('click', this.handle_selection_change, this);
            this.listbox.on('change', this.handle_selection_change, this);

            // Define our custom event.
            this.selectionempty = this.is_selection_empty();

            this.send_query(true);
        },

        /**
         * Key up hander for the search text box.
         * @param {Y.Event} e the keyup event.
         */
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
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selection_change : function() {
            var isselectionempty = this.is_selection_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('job_role_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
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

            var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/outcome/search.php', {
                method: 'POST',
                data: 'selectorid=' + hash + '&outcome=' + this.outcomeId + '&search' + '=' + value + '&sesskey=' + M.cfg.sesskey,
                on: {
                    complete: this.handle_response
                    //end: this.mark
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
            // Clear out the existing options, keeping any ones that are already selected.
            this.listbox.get('options').each(function(){
                this.remove();
            });

            // Output each optgroup.
            for (var key in data.results) {
                var outcomes = data.results[key];

                outcomes = outcomes.jr;
                for (var index in outcomes) {
                    var out = outcomes[index];

                    var option = Y.Node.create('<option value="' + out.id + '">' + out.name + '</option>');
                    this.listbox.append(option);
                }

                /* Mark selected    */
                this.listbox.set('selectedIndex',0);
            }

           this.handle_selection_change();
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
         * @param {string} name The name of the option to retrieve
         * @return the value of one of the option checkboxes.
         */
        get_option : function(name) {
            var checkbox = Y.one('#userselector_' + name + 'id');
            if (checkbox) {
                return (checkbox.get('checked'));
            } else {
                return false;
            }
        },


        mark: function() {
            if (remove_jr) {
                var users = remove_jr.split(",");

                if (this.name == 'removeselect') {
                    this.listbox.get("options").each( function() {
                        if (remove_jr) {
                            if (users.indexOf(this.get('value')) !== -1) {
                                this.setAttribute('selected','selected');
                            }else {
                                this.removeAttribute('selected');
                            }
                        }else {
                            this.removeAttribute('selected');
                        }
                    });
                }
            }
        }
    };


    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(job_role_selector, Y.EventTarget, null, null, {});
    // Initialise the user selector
    job_role_selector.init();
    // Store the user selector so that it can be retrieved
    this.job_roles_selectors[name] = job_role_selector;

    window.onbeforeunload = null;

    // Return the user selector
    return job_role_selector;
};