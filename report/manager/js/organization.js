/**
 * Report Competence Manager - Java Script - Company Structure Selector
 *
 * @package         report
 * @subpackage      manager/js
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/10/2015
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

M.core_user.init_organization = function (Y,name,employeeSel,outcomeSel,superUser,myAccess,btnActions) {
var level_structure = {
    /** Number of seconds to delay before submitting a query request */
    querydelay : 0.5,

    /* Level Zero Selector   */
    levelZero   : Y.one('#id_' + name + '0'),
    /* Level One Selector   */
    levelOne    : Y.one('#id_' + name + '1'),
    /* Level Two Selector   */
    levelTwo    : Y.one('#id_' + name + '2'),
    /* Level Three Selector */
    levelThree  : Y.one('#id_' + name + '3'),

    /* Employee Selector    */
    employeeLst : Y.one('#id_' + employeeSel) || null,

    /* Outcome Selector */
    outcomeLst : Y.one('#id_' + outcomeSel) || null,

    delEmployees : Y.one('#id_btn-delete_employees3'),

    delAllEmployees : Y.one('#id_btn-delete_all_employees3'),

    /* Super User   */
    sp_user     : superUser,
    /* Level Access */
    myLevelAccess : myAccess,
    /* Actions Button   */
    btnActions  : btnActions,

    /** Used to hold the timeout id of the timeout that waits before doing a search. */
    timeoutid : null,
    /** Stores any in-progress remote requests. */
    iotransactions : {},

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
        if (this.employeeLst) {
            this.levelThree.on('change', this.Load_Employees, this);
            this.delEmployees.on('click',this.Delete_Employees,this);
            this.delAllEmployees.on('click',this.Delete_All_Employees,this);
        }else if (this.outcomeLst) {
            this.levelThree.on('change', this.Load_Outcomes, this);
        }

        this.ActivateDeactivateActionButtons();
    },

    Activate_LevelOne : function(e) {
        var parent  = this.levelZero.get('value');
        var level   = 1;

        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
    },

    Activate_LevelTwo : function(e) {
        var parent      = this.levelOne.get('value');
        var level       = 2;

        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
    },

    Activate_LevelThree : function(e) {
        var parent  = this.levelTwo.get('value');
        var level   = 3;
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
    },

    Load_Employees : function (e) {
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_employees(false)}, this);
    },

    Delete_Employees : function (e) {
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_delete_employees(false)}, this);
    },

    Delete_All_Employees : function (e) {
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_delete_all_employees(false)}, this);
    },

    Load_Outcomes : function (e) {
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_outcomes(false)}, this);
    },

    /**
     * Fires off the ajax search request.
     */
    send_query : function(forceresearch,parent,level) {
        var levelZero = this.levelZero.get('value');
        // Cancel any pending timeout.
        this.cancel_timeout();

        // Try to cancel existing transactions.
        Y.Object.each(this.iotransactions, function(trans) {
            trans.abort();
        });

        var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/organization.php',
                           {
                            method: 'POST',
                            data: 'parent=' + parent + '&levelZero=' + levelZero + '&level' + '=' + level + '&sp=' + this.sp_user + '&sesskey=' + M.cfg.sesskey,
                            on: {
                                    complete: this.handle_response,
                                    end: this.ActivateDeactivateActionButtons
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
                            if (option.get('selected')) {
                                selected[option.get('value')] = option.get('value');
                            }

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


    send_query_employees : function(forceresearch) {
        var valueThree  = 0;

        // Cancel any pending timeout.
        this.cancel_timeout();

        // Try to cancel existing transactions.
        Y.Object.each(this.iotransactions, function(trans) {
            trans.abort();
        });


        /* Get Level Three  */
        this.levelThree.all('option').each(function(option){
            if (option.get('selected') && (option.get('value') != 0)) {
                if (valueThree == 0) {
                    valueThree = option.get('value');
                }else {
                    valueThree = valueThree + '#' + option.get('value');
                }
            }//seleted
        });

        var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/company_structure/employees.php',
            {
                method: 'POST',
                data: 'levelThree=' + valueThree + '&sesskey=' + M.cfg.sesskey,
                on: {
                    complete: this.handle_responseEmployees,
                    end: this.ActivateDeactivateActionButtons
                },
                context:this
            }
        );
        this.iotransactions[iotrans.id] = iotrans;
    },

    send_query_delete_employees : function(forceresearch) {
        var valueThree  = 0;
        var selEmployees    = 0;

        // Cancel any pending timeout.
        this.cancel_timeout();

        // Try to cancel existing transactions.
        Y.Object.each(this.iotransactions, function(trans) {
            trans.abort();
        });

        /* Level Three */
        valueThree = this.levelThree.get('value');

        /* Get Employees  */
        this.employeeLst.all('option').each(function(option){
            if (option.get('selected') && (option.get('value') != 0)) {
                if (selEmployees == 0) {
                    selEmployees = option.get('value');
                }else {
                    selEmployees = selEmployees + '#' + option.get('value');
                }
            }//seleted
        });

        var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/company_structure/employees.php',
            {
                method: 'POST',
                data: 'levelThree=' + valueThree + '&sesskey=' + M.cfg.sesskey + '&delete=1' + '&employees=' + selEmployees,
                on: {
                    complete: this.handle_responseDeleteEmployees
                },
                context:this
            }
        );
        this.iotransactions[iotrans.id] = iotrans;
    },

    send_query_delete_all_employees : function(forceresearch) {
        var valueThree      = 0;

        // Cancel any pending timeout.
        this.cancel_timeout();

        // Try to cancel existing transactions.
        Y.Object.each(this.iotransactions, function(trans) {
            trans.abort();
        });

        /* Level Three */
        valueThree = this.levelThree.get('value');

        var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/company_structure/employees.php',
            {
                method: 'POST',
                data: 'levelThree=' + valueThree + '&sesskey=' + M.cfg.sesskey + '&deleteAll=1',
                on: {
                    complete: this.handle_responseDeleteEmployees
                },
                context:this
            }
        );
        this.iotransactions[iotrans.id] = iotrans;
    },

    handle_responseDeleteEmployees : function(requestid, response) {
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
            this.send_query_employees();
        } catch (e) {
            this.levelThree.addClass('error');
            return new M.core.exception(e);
        }
    },

    /**
     * Handle what happens when we get some data back from the search.
     * @param {int} requestid not used.
     * @param {object} response the list of users that was returned.
     */
    handle_responseEmployees : function(requestid, response) {
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
            this.output_optionsEmployees(data);
        } catch (e) {
            this.levelThree.addClass('error');
            return new M.core.exception(e);
        }
    },

    /**
     * This method should do the same sort of thing as the PHP method
     * user_selector_base::output_options.
     * @param {object} data the list of users to populate the list box with.
     */
    output_optionsEmployees : function(data) {
        var index;
        var dataEmployees;
        var employees;
        var indexEmpl;
        var user;

        /* Clean the List Before Add the news   */
        this.employeeLst.all('option').each(function(option){
            option.remove();
        });


        // Clear out the existing options, keeping any ones that are already selected.
        for (index in data.results) {
            /* Get Employees    */
            dataEmployees   = data.results[index];
            employees       = dataEmployees.users;

            /* Add to the list  */
            for (indexEmpl in employees) {
                /* Get Info Employee    */
                user = employees[indexEmpl];

                var option = Y.Node.create('<option value="' + user.id + '">' + user.name + '</option>');

                this.employeeLst.append(option);
            }
        }//for_level
    },

    send_query_outcomes : function (forceresearch) {
        var valueZero   = this.levelZero.get('value') || 0;
        var valueOne    = this.levelOne.get('value') || 0;
        var valueTwo    = this.levelTwo.get('value') || 0;
        var valueThree  = this.levelTwo.get('value') || 0;

        // Cancel any pending timeout.
        this.cancel_timeout();

        // Try to cancel existing transactions.
        Y.Object.each(this.iotransactions, function(trans) {
            trans.abort();
        });

        var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/employee_report/outcomes.php',
            {
                method: 'POST',
                data: 'levelZero=' + valueZero + '&levelOne=' + valueOne + '&levelTwo=' + valueTwo + '&levelThree=' + valueThree + '&sesskey=' + M.cfg.sesskey,
                on: {
                    complete: this.handle_responseOutcomes,
                    end: this.ActivateDeactivateActionButtons
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
    handle_responseOutcomes : function(requestid, response) {
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
            this.output_optionsOutcomes(data);
        } catch (e) {
            this.levelThree.addClass('error');
            return new M.core.exception(e);
        }
    },

    /**
     * This method should do the same sort of thing as the PHP method
     * user_selector_base::output_options.
     * @param {object} data the list of users to populate the list box with.
     */
    output_optionsOutcomes : function(data) {
        var index;
        var dataOutcomes;
        var outcomes;
        var indexOut;
        var out;

        /* Clean the List Before Add the news   */
        this.outcomeLst.all('option').each(function(option){
            option.remove();
        });

        // Clear out the existing options, keeping any ones that are already selected.
        for (index in data.results) {
            /* Get Outcomes    */
            dataOutcomes   = data.results[index];
            outcomes       = dataOutcomes.outcomes;

            /* Add to the list  */
            for (indexOut in outcomes) {
                /* Get Info Employee    */
                out = outcomes[indexOut];

                var option = Y.Node.create('<option value="' + out.id + '">' + out.name + '</option>');

                this.outcomeLst.append(option);
            }
        }//for_level
    },

    ActivateDeactivateActionButtons : function () {
        if (this.btnActions) {
            /* Level Zero   */
            if (this.sp_user) {
                this.ActivateDeactivateActionButtons_SuperUser();
            }else {
                if (this.levelZero.get('value') == 0) {
                    Y.one('#id_btn-managers_selected0').setAttribute('disabled','disabled');
                    Y.one('#id_btn-reporters_selected0').setAttribute('disabled','disabled');
                    Y.one('#id_btn-rename_selected0').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_selected0').setAttribute('disabled','disabled');
                    Y.one('#id_btn-add_item1').setAttribute('disabled','disabled');
                }else {
                    Y.one('#id_btn-managers_selected0').removeAttribute('disabled');
                    Y.one('#id_btn-reporters_selected0').removeAttribute('disabled');
                    Y.one('#id_btn-rename_selected0').removeAttribute('disabled');
                    Y.one('#id_btn-delete_selected0').removeAttribute('disabled');
                    Y.one('#id_btn-add_item1').removeAttribute('disabled');
                }

                /* Level one    */
                if (this.levelOne.get('value') == 0) {
                    Y.one('#id_btn-managers_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-reporters_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-rename_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-add_item2').setAttribute('disabled','disabled');
                }else {
                    Y.one('#id_btn-managers_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-reporters_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-rename_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-delete_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-add_item2').removeAttribute('disabled');
                }

                /* Level Two    */
                if (this.levelTwo.get('value') == 0) {
                    Y.one('#id_btn-managers_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-reporters_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-rename_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-add_item3').setAttribute('disabled','disabled');
                }else {
                    Y.one('#id_btn-managers_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-reporters_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-rename_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-delete_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-add_item3').removeAttribute('disabled');
                }

                /* Level Three  */
                if (this.levelThree.get('value') == 0) {
                    Y.one('#id_btn-managers_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-reporters_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-rename_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_employees3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_all_employees3').setAttribute('disabled','disabled');
                }else {
                    Y.one('#id_btn-managers_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-reporters_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-rename_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-delete_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-delete_employees3').removeAttribute('disabled');
                    Y.one('#id_btn-delete_all_employees3').removeAttribute('disabled');
                }
            }
        }//ifbtnActions
    },

    ActivateDeactivateActionButtons_SuperUser :  function() {
        var accessZero  = 0;
        var accessOne   = 0;
        var accessTwo   = 0;
        var accessThree = 0;


        /* Level Zero   */
        Y.one('#id_btn-add_item0').setAttribute('disabled','disabled');
        Y.one('#id_btn-rename_selected0').setAttribute('disabled','disabled');
        Y.one('#id_btn-delete_selected0').setAttribute('disabled','disabled');
        Y.one('#id_btn-managers_selected0').setAttribute('disabled','disabled');
        Y.one('#id_btn-reporters_selected0').setAttribute('disabled','disabled');

        /* Level One    */
        Y.one('#id_btn-add_item1').setAttribute('disabled','disabled');
        Y.one('#id_btn-rename_selected1').setAttribute('disabled','disabled');
        Y.one('#id_btn-delete_selected1').setAttribute('disabled','disabled');
        Y.one('#id_btn-managers_selected1').setAttribute('disabled','disabled');
        Y.one('#id_btn-reporters_selected1').setAttribute('disabled','disabled');

        /* Level Two    */
        Y.one('#id_btn-add_item2').setAttribute('disabled','disabled');
        Y.one('#id_btn-rename_selected2').setAttribute('disabled','disabled');
        Y.one('#id_btn-delete_selected2').setAttribute('disabled','disabled');
        Y.one('#id_btn-managers_selected2').setAttribute('disabled','disabled');
        Y.one('#id_btn-reporters_selected2').setAttribute('disabled','disabled');

        /* Level Three  */
        Y.one('#id_btn-add_item3').setAttribute('disabled','disabled');
        Y.one('#id_btn-rename_selected3').setAttribute('disabled','disabled');
        Y.one('#id_btn-delete_selected3').setAttribute('disabled','disabled');
        Y.one('#id_btn-managers_selected3').setAttribute('disabled','disabled');
        Y.one('#id_btn-reporters_selected3').setAttribute('disabled','disabled');
        Y.one('#id_btn-delete_employees3').setAttribute('disabled','disabled');
        Y.one('#id_btn-delete_all_employees3').setAttribute('disabled','disabled');

        if (this.levelZero.get('value') != 0) {
            /* Get Level Zero   */
            accessZero = this.myLevelAccess[this.levelZero.get('value')];

            /* Get Access Level One */
            accessOne = accessZero.levelOne;
            if (accessOne == 0) {
                Y.one('#id_btn-add_item1').removeAttribute('disabled');
                if (this.levelOne.get('value') == 0) {
                    Y.one('#id_btn-managers_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-reporters_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-rename_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_selected1').setAttribute('disabled','disabled');
                    Y.one('#id_btn-add_item2').setAttribute('disabled','disabled');
                }else {
                    Y.one('#id_btn-managers_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-reporters_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-rename_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-delete_selected1').removeAttribute('disabled');
                    Y.one('#id_btn-add_item2').removeAttribute('disabled');
                }
            }
            /* Get Access Level Two */
            accessTwo = accessZero.levelTwo;
            if (accessTwo == 0) {
                if (this.levelOne.get('value') != 0) {
                    Y.one('#id_btn-add_item2').removeAttribute('disabled');
                }

                if (this.levelTwo.get('value') == 0) {
                    Y.one('#id_btn-managers_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-reporters_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-rename_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_selected2').setAttribute('disabled','disabled');
                    Y.one('#id_btn-add_item3').setAttribute('disabled','disabled');
                }else {
                    Y.one('#id_btn-managers_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-reporters_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-rename_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-delete_selected2').removeAttribute('disabled');
                    Y.one('#id_btn-add_item3').removeAttribute('disabled');
                }
            }

            /* Get Access Level Three   */
            accessThree = accessZero.levelThree;
            if (accessThree == 0) {
                if (this.levelTwo.get('value') != 0) {
                    Y.one('#id_btn-add_item3').removeAttribute('disabled');
                }

                if (this.levelThree.get('value') == 0) {
                    Y.one('#id_btn-managers_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-reporters_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-rename_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_selected3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_employees3').setAttribute('disabled','disabled');
                    Y.one('#id_btn-delete_all_employees3').setAttribute('disabled','disabled');
                }else {
                    Y.one('#id_btn-managers_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-reporters_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-rename_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-delete_selected3').removeAttribute('disabled');
                    Y.one('#id_btn-delete_employees3').removeAttribute('disabled');
                    Y.one('#id_btn-delete_all_employees3').removeAttribute('disabled');
                }
            }
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

