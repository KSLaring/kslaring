/**
 * Waiting List - Manual submethod Javascript
 *
 * @package         enrol/waitinglist
 * @subpackage      yui
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    11/09/2016
 * @author          efaktor     (fbv)
 *
 * Description
 * Structure Javascript
 */
// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.structure = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_structure = function (name) {
    return this.structure[name] || null;
};

M.core_user.init_structure = function (Y,name,reload,invoice) {
    var organization = {
        /** Number of seconds to delay before submitting a query request */
        querydelay  : 0.5,
        /* Level Zero Selector   */
        levelZero   : Y.one('#id_' + name + '0'),
        /* Level One Selector   */
        levelOne    : Y.one('#id_' + name + '1'),
        /* Level Two Selector   */
        levelTwo    : Y.one('#id_' + name + '2'),
        /* Level Three Selector */
        levelThree  : Y.one('#id_' + name + '3'),

        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,

        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},
        isManual : reload,
        isInvoice: invoice,

        /**
         * Initialises the user selector object
         * @constructor
         */
        init : function() {

            /* Level Zero  */
            this.levelZero.on('change', this.Activate_LevelOne, this);

            /* Level One    */
            this.levelOne.on('change', this.Activate_LevelTwo, this);

            /* Level Two    */
            this.levelTwo.on('change', this.Activate_LevelThree, this);

            /* Level Three  */
            if (this.isManual) {
                this.levelThree.on('change', this.Reload_ManualUsersSelectors, this);
            }else if (this.isInvoice) {
                this.levelThree.on('change', this.InvoiceDataCompany, this);
                if (this.levelTwo.get('value') != 0) {
                    this.InvoiceDataCompany();
                }
                this.DeactivateInvoiceDate();

                this.InvoiceDataCompany();
            }
        },

        Activate_LevelOne : function(e) {
            var parent  = this.levelZero.get('value');
            var level   = 1;

            //  Trigger an ajax search after a delay.
            this.cleanCookies();
            if (this.isInvoice) {
                this.DeactivateInvoiceDate();
            }
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        Activate_LevelTwo : function(e) {
            var parent      = this.levelOne.get('value');
            var level       = 2;

            //  Trigger an ajax search after a delay.
            this.cleanCookies();
            if (this.isInvoice) {
                this.DeactivateInvoiceDate();
            }
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        Activate_LevelThree : function(e) {
            var parent  = this.levelTwo.get('value');
            var level   = 3;

            //  Trigger an ajax search after a delay.
            this.cleanCookies();
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        Reload_ManualUsersSelectors : function() {
            if (this.isManual) {
                //  Trigger an ajax search after a delay.
                M.core_user.get_manual_user_selector('removeselect').reload_users();
                M.core_user.get_manual_user_selector('addselect').reload_users();
            }
        },

        InvoiceDataCompany: function(e) {
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();

            this.timeoutid = Y.later(this.querydelay * 1000, e,function(obj){obj.send_invoice_query(false)}, this);
        },

        send_invoice_query: function(forceresearch) {
            /* Variables */
            var levelTwo    = this.levelTwo.get('value');
            var levelThree  = this.levelThree.get('value');

            // Cancel any pending timeout.
            this.cancel_timeout();

            if (levelThree != 0) {
                // Try to cancel existing transactions.
                Y.Object.each(this.iotransactions, function(trans) {
                    trans.abort();
                });
                
                /* Activate Invoice Data Fields */
                this.ActivateInvoiceData();

                var iotrans = Y.io(M.cfg.wwwroot + '/enrol/waitinglist/invoicedata.php', {
                    method: 'POST',
                    data: 'two=' + levelTwo + '&three' + '=' + levelThree + '&sesskey=' + M.cfg.sesskey,
                    on: {
                        complete: this.handle_invoice_response
                    },
                    context:this
                });
                this.iotransactions[iotrans.id] = iotrans;
            }
        },

        handle_invoice_response : function(requestid, response) {
            try {
                delete this.iotransactions[requestid];
                if (!Y.Object.isEmpty(this.iotransactions)) {
                    // More searches pending. Wait until they are all done.
                    return;
                }
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.levelThree.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_invoice_options(data);
            } catch (e) {
                this.levelThree.addClass('error');
                return new M.core.exception(e);
            }
        },

        output_invoice_options : function(data) {
            var company;
            var infoInvoice;

            for (index in data.results) {
                /* Get Invoice data */
                company     = data.results[index];
                infoInvoice = company.invoice;

                /* Tjeneste */
                Y.one('#id_resp_number').set('value',infoInvoice.tjeneste);
                if (infoInvoice.tjeneste) {
                    Y.one('#id_resp_number').setAttribute('readonly');
                }else {
                    Y.one('#id_resp_number').removeAttribute('readonly');
                }

                /* Ansvar  */
                Y.one('#id_service_number').set('value',infoInvoice.ansvar);
                if (infoInvoice.ansvar) {
                    Y.one('#id_service_number').setAttribute('readonly');
                }else {
                    Y.one('#id_service_number').removeAttribute('readonly');
                }

                /* Address option Not viable */
                Y.one('#id_invoice_type_ADDRESS').setAttribute('disabled');
            }//for_results
        },
        
        ActivateInvoiceData: function() {
            Y.one('#id_invoice_type_ACCOUNT').set('checked','checked');
            /* Activate Account Invoice     */
            Y.one('#id_resp_number').removeAttribute('disabled');
            Y.one('#id_service_number').removeAttribute('disabled');
            Y.one('#id_project_number').removeAttribute('disabled');
            Y.one('#id_act_number').removeAttribute('disabled');
            Y.one('#id_resource_number').removeAttribute('disabled');

            /* Disabled Address Invoice     */
            Y.one('#id_street').setAttribute('disabled');
            Y.one('#id_post_code').setAttribute('disabled');
            Y.one('#id_city').setAttribute('disabled');
            Y.one('#id_bil_to').setAttribute('disabled');
        },

        DeactivateInvoiceDate: function() {
            Y.one('#id_invoice_type_ACCOUNT').set('checked','');
            /* Activate Account Invoice     */
            Y.one('#id_resp_number').setAttribute('disabled');
            Y.one('#id_resp_number').set('value','');

            Y.one('#id_service_number').setAttribute('disabled');
            Y.one('#id_service_number').set('value','');

            Y.one('#id_project_number').setAttribute('disabled');
            Y.one('#id_project_number').set('value','');

            Y.one('#id_act_number').setAttribute('disabled');
            Y.one('#id_act_number').set('value','');

            //Y.one('#id_resource_number').setAttribute('disabled');
            //Y.one('#id_resource_number').set('value','');
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch,parent,level) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            /* Check if it comes for manual sub-method enrollment or not */
            var manual;
            if (this.isManual) {
                manual = 1;
            }else {
                manual = 0;
            }
            var iotrans = Y.io(M.cfg.wwwroot + '/enrol/waitinglist/manualorganization.php', {
                method: 'POST',
                data: 'parent=' + parent + '&level' + '=' + level + '&manual=' + manual + '&sesskey=' + M.cfg.sesskey,
                on: {
                    complete: this.handle_response,
                    end: this.Reload_ManualUsersSelectors
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
                    this.levelZero.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.levelZero.addClass('error');
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

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {

                /* Get Data */
                dataSelector = data.results[index];

                /* Get level to update  */
                level = dataSelector.name;

                /* To Clean */
                var toClean = dataSelector.clean;
                for (var indexClean in toClean) {
                    var clean = toClean[indexClean];

                    Y.one("#id_" + clean).all('option').each(function(option){
                        if (option.get('value') != 0) {
                            option.remove();
                        }else {
                            option.setAttribute('selected','selected');
                        }
                    });
                }//for_clean

                /* Remove companies */
                Y.one("#id_" + level).all('option').each(function(option){
                    if (option.get('selected') ||option.get('value') == 0) {
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

                /* Mark selected    */
                Y.one("#id_" + level).get("options").each( function() {
                    if (selected[this.get('value')]) {
                        this.setAttribute('selected','selected');
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
        },

        cleanCookies : function () {
            document.cookie = "level_0" + "=0";
            document.cookie = "level_1" + "=0";
            document.cookie = "level_2" + "=0";
            document.cookie = "level_3" + "=0";
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(organization, Y.EventTarget, null, null, {});
    // Initialise the user selector
    organization.init();
    // Store the user selector so that it can be retrieved
    this.structure[name] = organization;

    window.onbeforeunload = null;

    // Return the user selector
    return organization;
};

