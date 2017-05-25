/**
 * Report Competence Manager - Java Script - Company Structure Selector - User Report
 *
 * @package         report
 * @subpackage      manager/user_report/js
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    24/05/2017
 * @author          eFaktor     (fbv)
 */

// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.organization = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_level_structure = function (name) {
    return this.organization[name] || null;
};

M.core_user.init_organization = function (Y,name) {
    var level_structure = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,

        // Level zero selector
        zero   : Y.one('#id_' + name + '0') || null,
        hZero  : Y.one('#id_h0') || null,
        // Level one selector
        one    : Y.one('#id_' + name + '1') || null,
        hOne   : Y.one('#id_h1') || null,
        // Level two selector
        two    : Y.one('#id_' + name + '2') || null,
        hTwo   : Y.one('#id_h2') || null,
        // Level three selector
        three  : Y.one('#id_' + name + '3') || null,

        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},

        /**
         * Initialises the user selector object
         * @constructor
         */
        init : function() {
            // Level zero
            this.zero.on('change', this.ActivateOne, this);

            // Level one
            if (this.one) {
                this.one.on('change', this.ActivateTwo, this);
            }

            // Level two
            if (this.two) {
                this.two.on('change', this.ActivateThree, this);
            }


            this.ini_default_values();
        },

        ini_default_values: function (e) {
            var valueThree = 0;

            this.hZero.set('value',this.zero.get('value'));
            this.hOne.set('value',this.one.get('value'));
            this.hTwo.set('value',this.two.get('value'));
        },

        ActivateOne : function(e) {
            this.hZero.set('value',this.zero.get('value'));
            this.hOne.set('value',0);
            this.hTwo.set('value',0);

            var parent  = this.zero.get('value');
            var level   = 1;
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        ActivateTwo : function(e) {
            this.hOne.set('value',this.one.get('value'));
            this.hTwo.set('value',0);

            var parent      = this.one.get('value');
            var level       = 2;
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        ActivateThree : function(e) {
            this.hTwo.set('value',this.two.get('value'));

            var parent  = this.two.get('value');
            var level   = 3;
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch,parent,level) {
            var levelZero = this.zero.get('value');
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/user_report/organization.php',
                {
                    method: 'POST',
                    data: 'parent=' + parent + '&levelZero=' + levelZero + '&level' + '=' + level  + '&sesskey=' + M.cfg.sesskey,
                    on: {
                        complete: this.handle_response
                    },
                    context:this
                }
            );
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
                    this.zero.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.zero.addClass('error');
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
            var selected = [];

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {

                /* Get Data */
                dataSelector = data.results[index];

                /* Get level to update  */
                level       = dataSelector.name;

                /* To Clean */
                var toClean = dataSelector.clean;
                for (var indexClean in toClean) {
                    var clean = toClean[indexClean];
                    if (Y.one("#id_" + clean)) {
                        Y.one("#id_" + clean).all('option').each(function(option){
                            if (option.get('value') != 0) {
                                option.remove();
                            }

                        });
                        Y.one("#id_" + clean).set('selectedIndex',0);
                    }
                }//for_clean

                /* Remove companies */
                Y.one("#id_" + level).all('option').each(function(option){
                    if (option.get('selected')) {
                        selected[option.get('value')] = option.get('value');
                    }
                    option.remove();
                });

                /* Add the new companies    */
                companies = dataSelector.items;
                for (indexCompany in companies) {
                    infoCompany = companies[indexCompany];

                    var option = Y.Node.create('<option value="' + infoCompany.id + '">' + infoCompany.name + '</option>');

                    Y.one("#id_" + level).append(option);
                }//for_companies


                Y.one("#id_" + level).all('option').each(function(option){
                    if (selected[option.get('value')]) {
                        option.setAttribute('selected','selected');
                    }else {
                        option.removeAttribute('selected');
                    }
                });
            }//for_level
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
    Y.augment(level_structure, Y.EventTarget, null, null, {});
    // Initialise the user selector
    level_structure.init();
    // Store the user selector so that it can be retrieved
    this.organization[name] = level_structure;

    window.onbeforeunload = null;

    // Return the user selector
    return level_structure;
};