/**
 * Microlearninig - Java Script - Users Selector
 *
 * @package         local
 * @subpackage      microlearning/js
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    10/11/2015
 * @author          eFaktor     (fbv)
 */

// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.micro_user_selectors = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_micro_user_selector = function (name) {
    return this.micro_user_selectors[name] || null;
};

M.core_user.init_micro_user_selector = function (Y, name, name_box, hash, lastsearch) {
    var micro_user_selector = {
        /** This id/name used for this control in the HTML. */
        name : name,
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /** The input element that contains the search term. */
        searchfield : Y.one('#' + name + '_searchtext'),

        /** The select element that contains the list of users. */
        listbox : Y.one('#id_' + name_box),
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
         * Initialises the user selector object
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


            var iotrans = Y.io(M.cfg.wwwroot + '/local/microlearning/users/search.php', {
                    method: 'POST',
                    data: 'selectorid=' + hash + '&search' + '=' + value + '&sesskey=' + M.cfg.sesskey,
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
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.listbox.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.listbox.setStyle('background','');
                this.listbox.addClass('error');
                return new M.core.exception(e);
            }
        },

        /**
         * This method should do the same sort of thing as the PHP method
         * user_selector_base::output_options.
         * @param {object} data the list of users to populate the list box with.
         */
        output_options : function(data) {
            var dataSelector;
            var users;
            var index;
            var indexUser;
            var infoUser;
            var selected = [];

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {

                /* Get Data */
                dataSelector = data.results[index];

                /* Get level to update  */
                users       = dataSelector.users;

                /* Remove Users */
                this.listbox.all('option').each(function(option){
                    if (option.get('selected')) {
                        selected[option.get('value')] = option.get('value');
                    }
                    option.remove();
                });

                for (indexUser in users) {
                    infoUser = users[indexUser];

                    var option = Y.Node.create('<option value="' + infoUser.id + '">' + infoUser.name + '</option>');

                    this.listbox.append(option);
                }

                /* Mark Users   */
                this.listbox.all('option').each(function(option){
                    if (selected[option.get('value')]) {
                        option.setAttribute('selected','selected');
                    }else {
                        option.removeAttribute('selected');
                    }
                });
            }//for_user

            this.listbox.setStyle('background','');
        },

        /**
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selection_change : function() {
            var isselectionempty = this.is_selection_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('micro_user_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
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
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(micro_user_selector, Y.EventTarget, null, null, {});
    // Initialise the user selector
    micro_user_selector.init();
    // Store the user selector so that it can be retrieved
    this.micro_user_selectors[name] = micro_user_selector;

    window.onbeforeunload = null;

    // Return the user selector
    return micro_user_selector;
};