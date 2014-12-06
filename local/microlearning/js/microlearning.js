/**
 * Created with JetBrains PhpStorm.
 * User: fbv
 * Date: 10.10.14
 * Time: 10:00
 * To change this template use File | Settings | File Templates.
 */

YUI().use('node', function(Y) {
    /* Search Add Activity  */
    Y.one('#id_search_add_act').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_activity = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'add_activities' tag    */
        Y.one('#id_add_activities').get('options').each( function(index) {
            if (this.ancestor('add_activities')) {
                this.unwrap();
                this.show();
            }//if_add_activities_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_add_activities").get("options").each( function() {
            var str_activity = this.get('text').toLowerCase();

            if (str_activity.indexOf('choose') == -1) {
                if (str_activity.indexOf(find_activity) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<add_activities id="add_activities_tag"></add_activities>');
                }
            }
        });

        Y.one("#id_search_add_act").focus();
        window.onbeforeunload = null;
    });

    /* Search Remove Activity   */
    Y.one('#id_search_sel_act').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_activity = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'sel_activities' tag    */
        Y.one('#id_sel_activities').get('options').each( function(index) {
            if (this.ancestor('sel_activities')) {
                this.unwrap();
                this.show();
            }//if_sel_activities_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_sel_activities").get("options").each( function() {
            var str_activity = this.get('text').toLowerCase();

            if (str_activity.indexOf('choose') == -1) {
                if (str_activity.indexOf(find_activity) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<sel_activities id="sel_activities_tag"></sel_activities>');
                }
            }
        });

        Y.one("#id_search_sel_act").focus();
        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});