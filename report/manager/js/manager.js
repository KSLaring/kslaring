/**
 * Competence Manager - Javascript
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    30/01/2015
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    var parentLevelZero;
    var parentLevelOne;
    var parentLevelTwo;
    var parentLevelThree;
    var indexSelected;
    var parentImportZero;
    var parentImportOne;
    var parentImportTwo;
    var parentImportLevel;
    var courseReport;
    var outcomeReport;


    /* Outcome Report    */
    if (Y.one('#id_outcome_list')) {
        Y.one('#id_outcome_list').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_outcome_list").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                outcomeReport = Y.one("#id_outcome_list").get('options').item(indexSelected).get('value');
            }else {
                outcomeReport = 0;
            }

            document.cookie = "outcomeReport"         + "=" + outcomeReport;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_course_report

    /* Course Report    */
    if (Y.one('#id_course_list')) {
        Y.one('#id_course_list').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_course_list").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                courseReport = Y.one("#id_course_list").get('options').item(indexSelected).get('value');
            }else {
                courseReport = 0;
            }

            document.cookie = "courseReport"         + "=" + courseReport;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_course_report

    /* Level Zero       */
    if (Y.one('#id_level_0')) {
        Y.one('#id_level_0').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_level_0").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentLevelZero =Y.one("#id_level_0").get('options').item(indexSelected).get('value');
            }else {
                parentLevelZero = 0;
            }

            //Save information in cookie
            if (Y.one('#id_job_role_name')) {
                document.cookie = "jobRole"                 + "=" + Y.one('#id_job_role_name').get('value');
                document.cookie = "industryCode"            + "=" + Y.one('#id_industry_code').get('value');
            }//if_exists

            document.cookie = "parentLevelZero"         + "=" + parentLevelZero;
            document.cookie = "parentLevelOne"          + "=0";
            document.cookie = "parentLevelTwo"          + "=0";
            document.cookie = "parentLevelThree"        + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_level_0

    /* Level One    */
    if (Y.one('#id_level_1')) {
        Y.one('#id_level_1').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_level_1").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentLevelOne =Y.one("#id_level_1").get('options').item(indexSelected).get('value');
            }else {
                parentLevelOne = 0;
            }

            //Save information in cookie
            document.cookie = "parentLevelOne"      + "=" + parentLevelOne;
            document.cookie = "parentLevelTwo"      + "=0";
            document.cookie = "parentLevelThree"    + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_level_1

    /* Level Two    */
    if (Y.one('#id_level_2')) {
        Y.one('#id_level_2').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_level_2").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentLevelTwo =Y.one("#id_level_2").get('options').item(indexSelected).get('value');
            }else {
                parentLevelTwo = 0;
            }

            //Save information in cookie
            document.cookie = "parentLevelTwo"      + "=" + parentLevelTwo;
            document.cookie = "parentLevelThree"    + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_level_2

    /* Level Three  */
    if (Y.one('#id_level_3')) {
        Y.one('#id_level_3').on('change', function (e) {
            var LevelThree = 0;
            Y.one("#id_level_3").get("options").each( function() {
                if (this.get('selected') && this.get('value') !== 0) {
                    if (LevelThree == 0) {
                        LevelThree = this.get('value');
                    }else {
                        LevelThree = LevelThree + ',' + this.get('value');
                    }
                }else {
                    parentLevelThree = 0;
                }//if_selected
            });

            //Save information in cookie
            document.cookie = "parentLevelThree"     + "=" + LevelThree;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_level_3

    /* Import Level  */
    if (Y.one('#id_import_level')) {
        Y.one('#id_import_level').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_import_level").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentImportLevel =Y.one("#id_import_level").get('options').item(indexSelected).get('value');
            }else {
                parentImportLevel = 0;
            }

            //Save information in cookie
            document.cookie = "parentImportLevel"       + "=" + parentImportLevel;
            document.cookie = "parentImportZero"        + "=0";
            document.cookie = "parentImportOne"         + "=0";
            document.cookie = "parentImportTwo"         + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_Import_Level

    /* Import Zero  */
    if (Y.one('#id_import_0')) {
        Y.one('#id_import_0').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_import_0").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentImportZero =Y.one("#id_import_0").get('options').item(indexSelected).get('value');
            }else {
                parentImportZero = 0;
            }

            //Save information in cookie
            document.cookie = "parentImportZero"        + "=" + parentImportZero;
            document.cookie = "parentImportOne"         + "=0";
            document.cookie = "parentImportTwo"         + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_import_0

    /* Import One   */
    if (Y.one('#id_import_1')) {
        Y.one('#id_import_1').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_import_1").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentImportOne =Y.one("#id_import_1").get('options').item(indexSelected).get('value');
            }else {
                parentImportOne = 0;
            }

            //Save information in cookie
            document.cookie = "parentImportOne"         + "=" + parentImportOne;
            document.cookie = "parentImportTwo"         + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_import_1

    /* Import Two   */
    if (Y.one('#id_import_2')) {
        Y.one('#id_import_2').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_import_2").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentImportTwo =Y.one("#id_import_2").get('options').item(indexSelected).get('value');
            }else {
                parentImportTwo = 0;
            }

            //Save information in cookie
            document.cookie = "parentImportTwo"         + "=" + parentImportTwo;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_import_2

    /* Get the Industry Code - Link Company */
    if (Y.one('#id_other_company')) {
        var linkCompany;
        var indexSel;
        var index;
        var industryCode;

        Y.one('#id_other_company').on('change', function (e) {
            /* Get Industry Code  */
            indexSel   = this.get('selectedIndex');
            if (Y.one("#id_other_company").get('options').item(indexSel).get('value') != 0) {
                linkCompany     = Y.one("#id_other_company").get('options').item(indexSel).get('text');
                index           = linkCompany.indexOf(" - ");
                industryCode    = linkCompany.substr(0,index);

                /* Set Industry Code */
                Y.one("#id_industry_code").set('value',industryCode);
            }else {
                Y.one("#id_industry_code").set('value','');
            }

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_other_company

    window.onbeforeunload = null;
});