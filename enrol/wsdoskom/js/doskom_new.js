/**
 * Single Sign On Enrolment Plugin - Search javascript
 *
 * @package         enrol
 * @subpackage      wsdoskom/js
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    27/02/2015
 * @author          efaktor     (fbv)
 *
 * Description
 *  Search company filter
 */
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.company_selectors = [];

M.core_user.get_company_selector = function (name) {
    return this.company_selectors[name] || null;
};

M.core_user.init_companies_selector = function (Y, name, hash, course, lastsearch) {
    var company_selector = {
        /** This id/name used for this control in the HTML. */
        name : name,
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /** The input element that contains the search term. */
        searchfield : Y.one('#' + name + '_searchtext'),
        /** The select element that contains the list of users. */
        listbox : Y.one('#' + name),
        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},
        /** The last string that we searched for, so we can avoid unnecessary repeat searches. */
        lastsearch : lastsearch,
        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,

        /* Course */
        courseId : course,

        /**
         * Initialises the user manager object
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
                this.fire('company_selector:selectionchanged', isselectionempty);
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

            var iotrans = Y.io(M.cfg.wwwroot + '/enrol/wsdoskom/search.php', {
                method: 'POST',
                data: 'course=' + this.courseId + '&search=' + value + '&selectorid=' + hash + '&sesskey=' + M.cfg.sesskey,
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
            }catch (e) {
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
            var level;
            var dataSelector;
            var companies;
            var index;
            var indexCompany;
            var infoCompany;
            var selected = {};
            
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
    Y.augment(company_selector, Y.EventTarget, null, null, {});
    // Initialise the user selector
    company_selector.init();
    // Store the user selector so that it can be retrieved
    this.company_selectors[name] = company_selector;

    window.onbeforeunload = null;

    // Return the user selector
    return company_selector;
};