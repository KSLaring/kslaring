/**
 * Report Competence Manager - Reporters Javascript
 *
 * Description
 *
 * @package         report/reporter
 * @subpackage      company_structure/reporter
 * @copyright       2010 eFaktor
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */

M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.reporter_selectors = [];

M.core_user.get_reporter_selector = function (name) {
    return this.reporter_selectors[name] || null;
};

M.core_user.init_reporters_selector = function (Y, name, hash, level, companies, lastsearch) {
    var reporter_selector = {
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
        /* Level */
        hierarchy : level,
        /* Company */
        companies : companies,

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
                this.fire('manager_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch) {
            var levelZero   =  this.companies[0];
            var levelOne    = (this.companies[1] || null);
            var levelTwo    = (this.companies[2] || null);
            var levelThree  = (this.companies[3] || null);

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

            var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/company_structure/reporter/search.php', {
                method: 'POST',
                data: 'level=' + level + '&levelzero=' + levelZero + '&levelone=' + levelOne + '&leveltwo=' + levelTwo + '&levelthree=' + levelThree + '&search=' + value + '&selectorid=' + hash + '&sesskey=' + M.cfg.sesskey,
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
            // Clear out the existing options, keeping any ones that are already selected.
            var selected_reporters = {};
            this.listbox.all('optgroup').each(function(optgroup){
                optgroup.all('option').each(function(option){
                    if (option.get('selected')) {
                        selected_reporters[option.get('value')] = {
                            id : option.get('value'),
                            name : option.get('innerText') || option.get('textContent'),
                            disabled: option.get('disabled')
                        }
                    }
                    option.remove();
                }, this);
                optgroup.remove();
            }, this);

            // Output each optgroup.
            var count = 0;
            for (var key in data.results) {
                var groupdata = data.results[key];

                this.output_group(groupdata.name, groupdata.users, selected_reporters, true);
                count ++;
            }

            if (!count) {
                var searchstr = (this.lastsearch != '') ? this.insert_search_into_str(M.str.moodle.nomatchingusers, this.lastsearch) : M.str.moodle.none;
                this.output_group(searchstr, {}, selected_reporters, true)
            }

            // If there were previously selected users who do not match the search, show them too.
            if (this.get_option('preserveselected') && selected_reporters) {
                this.output_group(this.insert_search_into_str(M.str.moodle.previouslyselectedusers, this.lastsearch), selected_reporters, true, false);
            }

            this.handle_selection_change();
        },

        /**
         * This method should do the same sort of thing as the PHP method
         * user_selector_base::output_optgroup.
         *
         * @param {string} groupname the label for this optgroup.v
         * @param {object} users the users to put in this optgroup.
         * @param {boolean|object} selectedusers if true, select the users in this group.
         * @param {boolean} processsingle
         */
        output_group : function(groupname, reporters, selected_reporters, processsingle) {
            var optgroup    = Y.Node.create('<optgroup></optgroup>');
            var count       = 0;

            for (var key in reporters) {
                var reporter    = reporters[key];
                var option      = Y.Node.create('<option value="' + reporter.id + '">' + reporter.name + '</option>');

                optgroup.append(option);
                if (reporter.infobelow) {
                    extraoption = Y.Node.create('<option disabled="disabled" class="userselector-infobelow"/>');
                    extraoption.appendChild(document.createTextNode(reporter.infobelow));
                    optgroup.append(extraoption);
                }
                count ++;
            }

            if (count > 0) {
                optgroup.set('label', groupname + ' (' + count + ')');
                if (processsingle && count === 1 && this.get_option('autoselectunique') && option.get('disabled') == false) {
                    option.set('selected', true);
                }
            } else {
                optgroup.set('label', groupname);
                optgroup.append(Y.Node.create('<option disabled="disabled">\u00A0</option>'));
            }
            this.listbox.append(optgroup);

            /* Mark selected    */
            this.listbox.get("options").each( function() {
                if (selected_reporters[this.get('value')]) {
                    this.setAttribute('selected','selected');
                }else {
                    this.removeAttribute('selected');
                }
            });
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
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(reporter_selector, Y.EventTarget, null, null, {});
    // Initialise the user selector
    reporter_selector.init();
    // Store the user selector so that it can be retrieved
    this.reporter_selectors[name] = reporter_selector;

    window.onbeforeunload = null;

    // Return the user selector
    return reporter_selector;
};