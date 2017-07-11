// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Fellesdata Integration - Javascript unconnect KS Organizations
 *
 * @package         local/fellesdata
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    16/02/2017
 * @author          eFaktor     (fbv)
 *
 */
// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.ksunconnect = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_ks_unconnected = function (name) {
    return this.ksunconnect[name] || null;
};

M.core_user.init_ks_unconnected = function (Y,name,sunconnect,removesearch,aunconnect,addsearch) {
    var ks_unconnect = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,

        selector :          Y.one('#id_' + name),
        // sunconnect
        toUnconnect:        Y.one('#id_' + sunconnect),
        searchToUnconnect : Y.one('#' + sunconnect + '_searchtext'),
        // aunconnect
        unconnect:          Y.one('#id_' + aunconnect),
        searchUnConnect :   Y.one('#' + aunconnect + '_searchtext'),

        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,

        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},

        /** The last string that we searched for, so we can avoid unnecessary repeat searches. */
        
        /**
         * Initialises the user selector object
         * @constructor
         */
        init : function() {
            // level selector change
            this.selector.on('change', this.load_ks_unconnected, this);

            // search event - to unconnect
            this.searchToUnconnect.on('keyup', this.handle_keyup, this);
            // search event - unconnected
            this.searchUnConnect.on('keyup', this.handle_keyup, this);
        },

        load_ks_unconnected: function(e,opt) {
            var level  = this.selector.get('value');

            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(level)}, this);
        }, //load_ks_unconnected

        /**
         * Key up hander for the search text box.
         * @param {Y.Event} e the keyup event.
         */
        handle_keyup : function(e) {
            var level  = this.selector.get('value');

            //  Trigger an ajax search after a delay.
            this.cancel_timeout();

            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_search(level)}, this);

            // If enter was pressed, prevent a form submission from happening.
            if (e.keyCode == 13) {
                e.halt();
            }
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(level) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var iotrans = Y.io(M.cfg.wwwroot + '/local/fellesdata/unconnected/ksunconnect.php', {
                method: 'POST',
                data: 'level=' + level + '&removesearch=' + removesearch + '&addsearch=' + addsearch + '&sesskey=' + M.cfg.sesskey,
                on: {
                    complete: this.handle_response
                },
                context:this
            });
            this.iotransactions[iotrans.id] = iotrans;
        },

        /**
         * Fires off the ajax search request.
         */
        send_query_search : function(level) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var rsearch  = this.searchToUnconnect.get('value').toString().replace(/^ +| +$/, '');
            var asearch  = this.searchUnConnect.get('value').toString().replace(/^ +| +$/, '');


            var iotrans = Y.io(M.cfg.wwwroot + '/local/fellesdata/unconnected/ksunconnect.php', {
                method: 'POST',
                data: 'level=' + level + '&removesearch=' + rsearch + '&addsearch=' + asearch + '&sesskey=' + M.cfg.sesskey,
                on: {
                    complete: this.handle_response
                },
                context:this
            });
            this.iotransactions[iotrans.id] = iotrans;
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
                    this.selector.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.selector.addClass('error');
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
            var index;
            // aunconnect
            var sel_unconnected;
            var selected_unconnected    = {};
            // sunconnect
            var sel_to_unconnect;
            var selected_to_unconnect   = {};


            // Clean - unconnected
            this.unconnect.all('option').each(function(option){
                if (option.get('selected')) {
                    selected_unconnected[option.get('value')] = option.get('value');
                }
                option.remove();
            });

            // Clean tounconnect
            this.toUnconnect.all('option').each(function(option){
                if (option.get('selected')) {
                    selected_to_unconnect[option.get('value')] = option.get('value');
                }
                option.remove();
            });

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {

                // GEt data
                dataSelector = data.results[index];

                // Unconnected
                sel_unconnected = dataSelector.unconnected;
                this.add_unconnected(sel_unconnected,selected_unconnected);

                // Tounconnect
                sel_to_unconnect = dataSelector.tounconnect;
                this.add_to_unconnect(sel_to_unconnect,selected_to_unconnect);
            }//for_results
        },

        add_unconnected: function(sel_unconnected,selected_unconnected) {
            var index;
            var info;

            for (index in sel_unconnected) {
                info = sel_unconnected[index];

                var option = Y.Node.create('<option value="' + info.id + '">' + info.name + '</option>');

                this.unconnect.append(option);
            }//for_companies

            /* Mark selected    */
            this.unconnect.get("options").each( function() {
                if (selected_unconnected[this.get('value')]) {
                    this.setAttribute('selected','selected');
                }
            });

        },
        
        add_to_unconnect: function(sel_to_unconnect,selected_to_unconnect) {
            var index;
            var info;

            for (index in sel_to_unconnect) {
                info = sel_to_unconnect[index];

                var option = Y.Node.create('<option value="' + info.id + '">' + info.name + '</option>');

                this.toUnconnect.append(option);
            }//for_companies

            /* Mark selected    */
            this.toUnconnect.get("options").each( function() {
                if (selected_to_unconnect[this.get('value')]) {
                    this.setAttribute('selected','selected');
                }
            });
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
    Y.augment(ks_unconnect, Y.EventTarget, null, null, {});
    // Initialise the user selector
    ks_unconnect.init();
    // Store the user selector so that it can be retrieved
    this.ksunconnect[name] = ks_unconnect;

    window.onbeforeunload = null;

    // Return the user selector
    return ks_unconnect;
};
