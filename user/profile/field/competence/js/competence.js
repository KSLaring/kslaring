/**
 * Extra Profile Field Competence - Javascript
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    28/01/2015
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    var parentLevelZero;
    var parentLevelOne;
    var parentLevelTwo;
    var parentLevelThree;
    var indexSelected;

    /* Level Zero   */
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
            document.cookie = "parentLevelZero"      + "=" + parentLevelZero;
            document.cookie = "parentLevelOne"       + "=0";
            document.cookie = "parentLevelTwo"       + "=0";
            document.cookie = "parentLevelThree"     + "=0";

            window.onbeforeunload = null;
            window.location = location.href;
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
            window.location = location.href;
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
            window.location = location.href;
        });
    }//if_level_2

    /* Level Three  */
    if (Y.one('#id_level_3')) {
        Y.one('#id_level_3').on('change', function (e) {
            var LevelThree = 0;
            Y.one("#id_level_3").get("options").each( function() {
                if (this.get('selected') && this.get('value') != 0) {
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
            window.location = location.href;
        });
    }//if_level_3
    window.onbeforeunload = null;
});
