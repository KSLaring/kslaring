/**
 * Competence Profile - Java Script
 *
 * @package         report
 * @subpackage      manager/course_report/js
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/10/2015
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

M.core_user.init_organization = function (Y,name,jr_selector,user) {
var level_structure = {
    /** Number of seconds to delay before submitting a query request */
    querydelay : 0.5,

    /* Level Zero Selector   */
    levelZero   : Y.one('#id_' + name + '0') || null,
    /* Level One Selector   */
    levelOne    : Y.one('#id_' + name + '1') || null,
    /* Level Two Selector   */
    levelTwo    : Y.one('#id_' + name + '2') || null,
    /* Level Three Selector */
    levelThree  : Y.one('#id_' + name + '3') || null,

    /* Job Roles - Sel  */
    jobRoleLst : Y.one('#id_' + jr_selector),

    /* User Id  */
    userId : user,

    /** Used to hold the timeout id of the timeout that waits before doing a search. */
    timeoutid : null,
    /** Stores any in-progress remote requests. */
    iotransactions : {},

    /**
     * Initialises the user selector object
     * @constructor
     */
    init : function() {
        /* Level Zero       */
        this.levelZero.on('change', this.Activate_LevelOne, this);

        /* Level One    */
        this.levelOne.on('change', this.Activate_LevelTwo, this);

        /* Level Two    */
        this.levelTwo.on('change', this.Activate_LevelThree, this);

        /* Level Three  */
        this.levelThree.on('change', this.Load_JobRoles, this);
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


    Load_JobRoles : function (e) {
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_job_roles(false)}, this);
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

        var iotrans = Y.io(M.cfg.wwwroot + '/user/profile/field/competence/actions/organization.php',
                           {
                            method: 'POST',
                            data: 'parent=' + parent + '&level' + '=' + level + '&id=' + this.userId + '&sesskey=' + M.cfg.sesskey,
                            on: {
                                    complete: this.handle_response,
                                    end: this.Load_JobRoles
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
     * Fires off the ajax search request.
     */
    send_query_job_roles : function(forceresearch) {
        // Cancel any pending timeout.
        this.cancel_timeout();

        // Try to cancel existing transactions.
        Y.Object.each(this.iotransactions, function(trans) {
            trans.abort();
        });


        var valueZero   = this.levelZero ? this.levelZero.get('value') : 0;
        var valueOne    = this.levelOne ? this.levelOne.get('value') : 0;
        var valueTwo    = this.levelTwo ? this.levelTwo.get('value') : 0;
        var valueThree  = 0;


        if (this.levelThree) {
            this.levelThree.all('option').each(function(option){
                if (option.get('selected') && (option.get('value') != 0)) {
                    if (valueThree == 0) {
                        valueThree = option.get('value');
                    }else {
                        valueThree = valueThree + '#' + option.get('value');
                    }
                }//seleted
            });
        }//if_levleThree


        var iotrans = Y.io(M.cfg.wwwroot + '/user/profile/field/competence/actions/jobrole.php',
            {
                method: 'POST',
                data: 'levelZero=' + valueZero + '&levelOne=' + valueOne + '&levelTwo=' +  valueTwo + '&levelThree=' + valueThree + '&sesskey=' + M.cfg.sesskey,
                on: {
                     complete: this.handle_responseJobRoles
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
    handle_responseJobRoles : function(requestid, response) {
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
            this.output_optionsJobRoles(data);
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
    output_optionsJobRoles : function(data) {
        var jrSelector;
        var index;
        var selected = [];
        var jobRoles;
        var indexJR;
        var infoJR;

        // Clear out the existing options, keeping any ones that are already selected.
        for (index in data.results) {

            /* Get Data */
            jrSelector = data.results[index];

            /* Clean Selector */
            this.jobRoleLst.all('option').each(function(option){
                if (option.get('selected')) {
                    selected[option.get('value')] = option.get('test');
                }
                option.remove();
            });

            /* Add new Job Roles    */
            jobRoles = jrSelector.jr;
            for (indexJR in jobRoles) {
                infoJR = jobRoles[indexJR];

                var option = Y.Node.create('<option value="' + infoJR.id + '">' + infoJR.name + '</option>');

                this.jobRoleLst.append(option);
            }

            this.jobRoleLst.all('option').each(function(option){
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

