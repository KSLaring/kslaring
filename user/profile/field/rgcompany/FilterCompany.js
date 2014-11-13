/**
 * Company Filter - Javascript
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field
 * @copyright       2014 eFaktor    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    11/11/2014
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    /* Level One    */
    var GetSelectedOne;
    var ActivateLevelOne;
    var DeactivateLevelOne;
    /* Level Two    */
    var GetSelectedTwo;
    var ActivateLevelTwo;
    var DeactivateLevelTwo;
    /* Level Three  */
    var ActivateLevelThree;
    var DeactivateLevelThree;
    var SaveLevelThree;
    var RecuperateLevelThree;

    /* Job Role */
    var ActivateJobRoleCounty;
    var ActivateJobRoleCountyLevelOne;
    var ActivateJobRoleCountyLevelOneTwo;
    var ActivateJobRoleCountyLevelOneTwoThree;
    var ActivateJobRole;
    var DeactivateJobRole;
    var SaveJobRole;
    var RecuperateJobRole;

    /**************/
    /* County     */
    /**************/

    /**************/
    /* LEVEL ONE  */
    /**************/

    /* Get the selected value from Level One   */
    GetSelectedOne = function() {
        var levelOne;
        var index;
        var parentID;

        Y.one("#id_level_one").get("options").each( function() {
            if (this.get('selected') && this.get('value') != 0) {
                levelOne = this.get('value');
                /* Get Parent Id    */
                index       = levelOne.indexOf("_");
                parentID    = levelOne.substr(index+1);
            }else {
                this.set('selected',false);
                this.removeAttribute('selected');
            }///if_selected
        });

        return [parentID];
    };//GetSelectedOne

    /* Deactivate Level One */
    DeactivateLevelOne = function() {
        Y.one("#id_level_one").setAttribute('disabled');
        Y.one("#id_level_one").get("options").each( function() {
            if (this.ancestor('levelOne_tag')) {
                this.unwrap();
                this.show();
            }//if_levelOne_tag
            this.set('selected',false);
            this.removeAttribute('selected');
        });
    };//DeactivateLevelOne

    /* Activate Level   One */
    ActivateLevelOne = function(county) {
        var levelOne;

        /* Deactivate Levels   */
        DeactivateLevelThree();
        DeactivateLevelTwo();
        DeactivateLevelOne();

        /* Activate Level */
        Y.one("#id_level_one").removeAttribute('disabled');
        Y.one("#id_level_one").get("options").each( function() {
            /* Get Company Ref  */
            levelOne = this.get('value');
            /* Get Company ID   */
            if (levelOne != 0) {
                if (levelOne.indexOf(county) == -1) {
                    this.set('selected',false);
                    this.removeAttribute('selected');
                    this.wrap('<levelOne_tag id="levelOne_tag"></levelOne_tag>');
                }//if_different_county
            }//if_levelOne
        });
    };//ActivateLevelOne


    /**************/
    /* LEVEL TWO  */
    /**************/

    /* Set the selected value from Level Two   */
    GetSelectedTwo = function() {
        var levelTwo;
        var index;
        var parentID;

        Y.one("#id_level_two").get("options").each( function() {
            if (this.get('selected') && this.get('value') != 0) {
                levelTwo = this.get('value');
                /* Get Parent Id    */
                index       = levelTwo.indexOf("_");
                parentID    = levelTwo.substr(index+1);
            }else {
                this.set('selected',false);
                this.removeAttribute('selected');
            }///if_selected
        });

        return [parentID];
    };//GetSelectedTwo

    /* Deactivate Level Two */
    DeactivateLevelTwo = function() {
        Y.one("#id_level_two").setAttribute('disabled');
        Y.one("#id_level_two").get("options").each( function() {
            this.set('selected',false);
            this.removeAttribute('selected');
            if (this.ancestor('levelTwo_tag')) {
                this.unwrap();
                this.show();
            }//if_levelTwo_tag
        });
    };//DeactivateLevelTwo

    /* Activate Level   Two */
    ActivateLevelTwo = function() {
        var levelTwo;
        var levelOne;

        /* Get Level One    */
        levelOne = 'P' + GetSelectedOne() +'_';

        /* Deactivate Levels    */
        DeactivateLevelThree();
        DeactivateLevelTwo();

        /* Activate Level       */
        if (levelOne != 0) {
            Y.one("#id_level_two").removeAttribute('disabled');
            Y.one("#id_level_two").get("options").each( function() {
                /* Get Company Ref  */
                levelTwo = this.get('value');

                /* Get Company ID   */
                if (levelTwo != 0) {
                    if (levelTwo.indexOf(levelOne) == -1) {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                        this.wrap('<levelTwo_tag id="levelTwo_tag"></levelTwo_tag>');
                    }//if_different_parent
                }//if_levelTwo
            });
        }//if_levelOne
    };//ActivateLevelTwo

    /****************/
    /* LEVEL THREE  */
    /****************/

    /* Deactivate Level Three */
    DeactivateLevelThree = function() {
        Y.one("#id_profile_field_rgcompany").setAttribute('disabled');
        Y.one("#id_profile_field_rgcompany").removeAttribute('multiple');
        Y.one("#id_profile_field_rgcompany").get("options").each( function() {
            this.set('selected',false);
            this.removeAttribute('selected');
            if (this.ancestor('levelThree_tag')) {
                this.unwrap();
                this.show();
            }//if_levelTwo_tag
            Y.one("#id_hidden_level_three").set('value',0);
        });
    };//DeactivateLevelThree

    /* Activate Level   Three */
    ActivateLevelThree = function() {
        var levelTwo;
        var levelThree;

        /* Get Level Two    */
        levelTwo = 'P' + GetSelectedTwo() + '_';

        /* Deactivate Levels    */
        DeactivateLevelThree();

        /* Activate Level       */
        if (levelTwo != 0) {
            Y.one("#id_profile_field_rgcompany").removeAttribute('disabled');
            Y.one("#id_profile_field_rgcompany").setAttribute('multiple');
            Y.one("#id_profile_field_rgcompany").get("options").each( function() {
                /* Get Company Ref  */
                levelThree = this.get('value');

                /* Get Company ID   */
                if (levelThree != 0) {
                    if (levelThree.indexOf(levelTwo) == -1) {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                        this.wrap('<levelThree_tag id="levelThree_tag"></levelThree_tag>');
                    }//if_different_parent
                }//if_levelThree
            });
        }//if_level_two
    };//ActivateLevelThree

    /* Save the level three has been selected */
    SaveLevelThree = function() {
        var ref;
        var indexRef;
        var refReturn = new Array();

        /* Get the level three */
        Y.one('#id_hidden_level_three').set('value',0);
        Y.one("#id_profile_field_rgcompany").get("options").each( function() {
            var levelThree;

            /* Save the Level Three */
            if (this.get('selected')) {
                if (Y.one('#id_hidden_level_three').get('value') != 0) {
                    levelThree = Y.one('#id_hidden_level_three').get('value') + ',' + this.get('value');
                }else {
                    levelThree = this.get('value');
                }//if_else_hidden_level_three

                ref         = this.get('value');
                indexRef    = ref.indexOf('_');
                ref         = ref.substr(indexRef+1);

                refReturn.push('I3#' + ref + '#L3');

                /* Save the new Level Three selected    */
                Y.one('#id_hidden_level_three').set('value',levelThree);
            }//if_selected
        });

        return refReturn;
    };//SaveLevelThree

    /* Recuperate level three */
    RecuperateLevelThree = function() {
        var parentThree;
        var levelThree;
        var companyRef;
        var index;

        /* Select Level Three  */
        levelThree      = Y.one('#id_hidden_level_three').get('value');
        /* Get Parent Level Two */
        index           = levelThree.indexOf('_');
        parentThree     = levelThree.substr(0,index);
        /* Select Level Two */
        if (levelThree != 0) {
            Y.one("#id_profile_field_rgcompany").get("options").each( function() {
                if (this.get('value') != 0) {
                    companyRef  = this.get('value') + '#';
                    if (levelThree.indexOf(companyRef) != -1) {
                        this.set('selected',true);
                        this.setAttribute('selected');
                    }else {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                    }//if_companyREf

                    if (companyRef.indexOf(parentThree) == -1) {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                        this.wrap('<levelThree_tag id="levelThree_tag"></levelThree_tag>');
                    }//if_parent
                }//if_!=_0
            });
        }//if_levelThree_!=_0
    };//RecuperateLevelThree

    /************/
    /* JOB ROLE */
    /************/

    /* Deactivate Job Role  */
    DeactivateJobRole = function() {
        if (Y.one("#id_profile_field_rgjobrole")) {
            Y.one("#id_profile_field_rgjobrole").removeAttribute('multiple');
            Y.one("#id_profile_field_rgjobrole").setAttribute('disabled');

            Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
                this.set('selected',false);
                this.removeAttribute('selected');
                if (this.ancestor('JobRole_tag')) {
                    this.unwrap();
                    this.show();
                }//if_levelTwo_tag
            });
        }
    };//

    /* Activate Job Role By County  */
    ActivateJobRoleCounty = function(county) {
        var jrRef;

        /* Activate Job Role Level  */
        Y.one("#id_profile_field_rgjobrole").removeAttribute('disabled');
        Y.one("#id_profile_field_rgjobrole").setAttribute('multiple');
        Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
            jrRef = this.get('value');

            if (jrRef != 0) {
                this.set('selected',false);
                this.removeAttribute('selected');
                if (jrRef.indexOf(county + '_') == -1) {
                    this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                }//if_county
            }//if_!=_0
        });
    };//ActivateJobRoleCounty

    /* Activate Job Role by County and Level One    */
    ActivateJobRoleCountyLevelOne = function(county,levelOne) {
        var jrRef;

        /* Activate Job Role Level  */
        Y.one("#id_profile_field_rgjobrole").removeAttribute('disabled');
        Y.one("#id_profile_field_rgjobrole").setAttribute('multiple');
        Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
            jrRef = this.get('value');

            if (jrRef != 0) {
                this.set('selected',false);
                this.removeAttribute('selected');
                if (jrRef.indexOf(county) == -1) {
                    this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                }else {
                    if (jrRef.indexOf(county + '_0_0_0') == -1) {
                        if (jrRef.indexOf(levelOne) == -1) {
                            this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                        }
                    }//if_levelOne
                }//if_different_county
            }//if_!=_0
        });
    };//ActivateJobRoleCountyLevelOne

    /* Activate Job Role by County, Level One and Two   */
    ActivateJobRoleCountyLevelOneTwo = function(county,levelOne,levelTwo) {
        var jrRef;

        /* Activate Job Role Level  */
        Y.one("#id_profile_field_rgjobrole").removeAttribute('disabled');
        Y.one("#id_profile_field_rgjobrole").setAttribute('multiple');
        Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
            jrRef = this.get('value');

            if (jrRef != 0) {
                this.set('selected',false);
                this.removeAttribute('selected');
                if (jrRef.indexOf(county) == -1) {
                    this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                }else {
                    if (jrRef.indexOf(county + '_0_0_0') == -1) {
                        if (jrRef.indexOf(levelOne + '0_0') == -1) {
                            if (jrRef.indexOf(levelTwo) == -1) {
                                this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                            }
                        }//if_different_levelOne
                    }//if_levelOne
                }//if_different_county
            }//if_!=_0
        });
    };//ActivateJobRoleCountyLevelOneTwo

    /* Activate Job Role by County, Level One, Two and Three */
    ActivateJobRoleCountyLevelOneTwoThree = function(levelThree) {
        var county;
        var indexCounty;
        var levelOne;
        var levelTwo;
        var jrRef;
        var indexsJR = new Array();

        /* Deactivate Job Role Level */
        DeactivateJobRole();

        /* Activate Job Role    */
        if (Y.one("#id_profile_field_rgjobrole")) {
            /* Get County   */
            indexCounty = Y.one("#id_county").get('selectedIndex');
            county      = '#' + Y.one("#id_county").get('options').item(indexCounty).get('value') + '#C';
            /* Get Level One    */
            levelOne = 'I1#' + GetSelectedOne() + '#L1_';
            /* Get Level Two    */
            levelTwo = 'I2#' + GetSelectedTwo() + '#L2_';

            /* Get the Job Roles to activate  */
            Y.Array.each(levelThree,function(levelJR) {
                Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
                    jrRef = this.get('value');

                    if (jrRef != 0) {
                        if (jrRef.indexOf(levelJR) > -1) {
                            indexsJR.push(jrRef);
                        }
                    }//if_!=_0
                });
            });

            /* Activate Job Role Level  */
            Y.one("#id_profile_field_rgjobrole").removeAttribute('disabled');
            Y.one("#id_profile_field_rgjobrole").setAttribute('multiple');
            Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
                jrRef = this.get('value');

                if (jrRef != 0) {
                    this.set('selected',false);
                    this.removeAttribute('selected');
                    if (jrRef.indexOf(county) == -1) {
                        this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                    }else {
                        if (jrRef.indexOf(county + '_0_0_0') == -1) {
                            if (jrRef.indexOf(levelOne + '0_0') == -1) {
                                if (jrRef.indexOf(levelTwo + '0') == -1) {
                                    if (indexsJR.indexOf(jrRef) == -1) {
                                        this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                                    }
                                }
                            }//if_different_levelOne
                        }//if_levelOne
                    }//if_different_county
                }//if_!=_0
            });
        }//if_rgjobrole
    };//ActivateJobRoleCountyLevelOneTwoThree

    /* Activate Job Role    */
    ActivateJobRole = function() {
        var county;
        var indexCounty;
        var levelOne;
        var levelTwo;

        if (Y.one("#id_profile_field_rgjobrole")) {
            /* Deactivate Job Role Level */
            DeactivateJobRole();

            /* Get County   */
            indexCounty = Y.one("#id_county").get('selectedIndex');
            county      = '#' + Y.one("#id_county").get('options').item(indexCounty).get('value') + '#C';
            /* Get Level One    */
            levelOne = 'I1#' + GetSelectedOne() + '#L1_';
            /* Get Level Two    */
            levelTwo = 'I2#' + GetSelectedTwo() + '#L2_';

            /* Only County  */
            if ((county != '##C') && (levelOne == 'I1##L1_') && (levelTwo == 'I2##L2_')) {
                ActivateJobRoleCounty(county);
            }//if_only_County

            /* County and Level One */
            if ((county != '##C') && (levelOne != 'I1##L1_') && (levelTwo == 'I2##L2_')) {
                ActivateJobRoleCountyLevelOne(county,levelOne);
            }//if_county_levelOnes

            /* County and Level One and Level Two   */
            if ((county != '##C') && (levelOne != 'I1##L1_') && (levelTwo != 'I2##L2_')) {
                ActivateJobRoleCountyLevelOneTwo(county,levelOne,levelTwo);
            }//if_county_levelOnes
        }//if_rgjobrole
    };//ActivateJobRole

    /* Recuperate Job Role */
    RecuperateJobRole = function() {
        var jobRole;
        var index;
        var county;
        var levelOne;
        var levelTwo;

        /* Select Job Role  */
        jobRole      = Y.one('#id_hidden_job_role').get('value');

        /* Deactivate Job Role Level */
        DeactivateJobRole();

        /* Activate Job Role */
        if (jobRole != 0) {
            /* Get County       */
            index   = jobRole.indexOf('#JR_');
            county  = jobRole.substr(index+4);
            index   = county.indexOf('#C_');
            county  = county.substr(0,index+3);
            /* Get Level One    */
            index       = jobRole.indexOf('I1');
            levelOne    = jobRole.substr(index);
            index       = levelOne.indexOf('#L1_');
            levelOne    = levelOne.substr(0,index+4);
            /* Get Level Two    */
            index       = jobRole.indexOf('I2');
            levelTwo    = jobRole.substr(index);
            index       = levelTwo.indexOf('#L2_');
            levelTwo    = levelTwo.substr(0,index+4);

            /* Activate Job Role Level  */
            Y.one("#id_profile_field_rgjobrole").removeAttribute('disabled');
            Y.one("#id_profile_field_rgjobrole").setAttribute('multiple');
            Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
                var jrRef;

                this.set('selected',false);
                this.removeAttribute('selected');

                if (this.get('value') != 0) {
                    jrRef = this.get('value');

                    if (jobRole.indexOf(this.get('value')) == -1) {
                        if (jrRef.indexOf(county) == -1) {
                            this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                        }else {
                            if (jrRef.indexOf(county + '0_0_0') == -1) {
                                if (jrRef.indexOf(levelOne + '0_0') == -1) {
                                    if (jrRef.indexOf(levelTwo + '0') == -1) {
                                        this.wrap('<JobRole_tag id="JobRole_tag"></JobRole_tag>');
                                    }
                                }//if_different_levelOne
                            }
                        }//if_!=_county
                    }else {
                        this.set('selected',true);
                        this.setAttribute('selected');
                    }//if_parent
                }//if_!=_0
            });
        }//if_levelThree_!=_0

        Y.one('#id_hidden_job_role').set('value',0);
    };//RecuperateJobRole

    /* Save Job Roles Selected  */
    SaveJobRole = function() {
        /* Get Job Roles  */
        Y.one('#id_hidden_job_role').set('value',0);
        Y.one("#id_profile_field_rgjobrole").get("options").each( function() {
            var jobRoles;

            /* Save the JobRoles selected */
            if (this.get('selected')) {
                if (Y.one('#id_hidden_job_role').get('value') != 0) {
                    jobRoles = Y.one('#id_hidden_job_role').get('value') + ',' + this.get('value');
                }else {
                    jobRoles = this.get('value');
                }//if_else_hidden_job_role

                /* Save the new jobRole selected    */
                Y.one('#id_hidden_job_role').set('value',jobRoles);
            }//if_selected
        });
    };//SaveJobRole

    /*********************/
    /* EVENTS TO CAPTURE */
    /*********************/

    /* County --> Activate Level One    */
    if (Y.one('#id_county')) {
        Y.one('#id_county').on('change', function (e) {
            var county;

            /* Get County ID    */
            county = Y.one('#id_county').get('value') + '_';
            /* Activate Level One       */
            ActivateLevelOne(county);

            /* Activate Job Role      */
            ActivateJobRole();

            Y.one("#id_level_one").focus();
            window.onbeforeunload = null;
        });
    }//if_id_county

    /* Level One --> Activate Level Two */
    if (Y.one('#id_level_one')) {
        Y.one('#id_level_one').on('change', function (e) {
            /* Activate Level Two   */
            ActivateLevelTwo();

            /* Activate Job Role    */
            ActivateJobRole();

            Y.one("#id_level_two").focus();
            window.onbeforeunload = null;
        });
    }//if_level_one

    /* Level Two --> Activate Level Three   */
    if (Y.one('#id_level_two')) {
        Y.one('#id_level_two').on('change', function (e) {
            /* Activate Level Three */
            ActivateLevelThree();

            /* Activate Job Role    */
            ActivateJobRole();

            Y.one("#id_profile_field_rgcompany").focus();
            window.onbeforeunload = null;
        });
    }//if_level_two

    /* Save Level Three */
    if (Y.one('#id_profile_field_rgcompany')) {
        RecuperateLevelThree();

        Y.one('#id_profile_field_rgcompany').on('change', function (e) {
            var levelThree;

            /* Save Level Three */
            levelThree = SaveLevelThree();

            /* Activate Job Roles   */
            if (levelThree) {
                ActivateJobRoleCountyLevelOneTwoThree(levelThree);
            }

            window.onbeforeunload = null;
        });
    }//if_level_three

    /* Save Job Roles */
    if (Y.one("#id_profile_field_rgjobrole")) {
        RecuperateJobRole();

        Y.one('#id_profile_field_rgjobrole').on('change', function (e) {
            SaveJobRole();

            window.onbeforeunload = null;
        });

    }//if_jobrole
    window.onbeforeunload = null;
});