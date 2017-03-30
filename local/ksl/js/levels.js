// Define the core_user namespace if it has not already been defined.
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace.
M.core_user.organization = [];

M.core_user.get_level_structure = function (name) {
    return this.organization[name] || null;
};

M.core_user.init_organization = function (Y, sel_levelzero, sel_levelone, sel_leveltwo, sel_levelthree) {
    var level_structure = {
        querydelay : 0.5,

        // Level Zero!
        levelZero   : Y.one('#id_' + sel_levelzero),
        // Level One!
        levelOne    : Y.one('#id_' + sel_levelone),
        // Level Two!
        levelTwo    : Y.one('#id_' + sel_leveltwo),
        // Level Three!
        levelThree  : Y.one('#id_' + sel_levelthree),

        timeoutid : null,
        iotransactions : {},

        init : function() {

            /* Level Zero  */
            this.levelZero.on('change', this.Activate_LevelOne, this);

            /* Level One    */
            this.levelOne.on('change', this.Activate_LevelTwo, this);

            /* Level Two    */
            this.levelTwo.on('change', this.Activate_LevelThree, this);
        },

        Activate_LevelOne : function(e) {
            var parent  = this.levelZero.get('value');
            var level   = 1;

            Y.one('#id_level_1').removeAttribute('disabled');
            Y.one('#id_level_2').setAttribute('disabled', 'disabled');
            Y.one('#id_level_3').setAttribute('disabled', 'disabled');

            // Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        Activate_LevelTwo : function(e) {
            var parent  = this.levelOne.get('value');
            var level   = 2;

            Y.one('#id_level_2').removeAttribute('disabled');
            Y.one('#id_level_3').setAttribute('disabled', 'disabled');

            // Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        Activate_LevelThree : function(e) {
            var parent  = this.levelTwo.get('value');
            var level   = 3;

            Y.one('#id_level_3').removeAttribute('disabled');

            // Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
        },

        send_query : function(forceresearch,parent,level) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var iotrans = Y.io(M.cfg.wwwroot + '/local/ksl/reports/organization.php',
                {
                    method: 'POST',
                    data: 'parent=' + parent + '&level' + '=' + level + '&sesskey=' + M.cfg.sesskey,
                    on: {
                        complete: this.handle_response
                    },
                    context:this
                }
            );
            this.iotransactions[iotrans.id] = iotrans;
        },

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
                level = dataSelector.name;

                /* To Clean  */
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

        send_query_outcomes : function (forceresearch) {
            var valueZero   = this.levelZero.get('value') || 0;
            var valueOne    = this.levelOne.get('value') || 0;
            var valueTwo    = this.levelTwo.get('value') || 0;
            var valueThree  = this.levelThree.get('value') || 0;

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
                        complete: this.handle_responseOutcomes
                    },
                    context:this
                }
            );
            this.iotransactions[iotrans.id] = iotrans;
        },

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

        cancel_timeout : function() {
            if (this.timeoutid) {
                clearTimeout(this.timeoutid);
                this.timeoutid = null;
            }
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events.
    Y.augment(level_structure, Y.EventTarget, null, null, {});
    // Initialise the user selector.
    level_structure.init();
    // Store the user selector so that it can be retrieved.
    this.organization[name] = level_structure;

    window.onbeforeunload = null;

    // Return the user selector.
    return level_structure;
};