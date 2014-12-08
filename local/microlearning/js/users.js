/**
 * Created with JetBrains PhpStorm.
 * User: fbv
 * Date: 14.10.14
 * Time: 12:52
 * To change this template use File | Settings | File Templates.
 */
YUI().use('node', function(Y) {
    /* Search Add Users     */
    Y.one('#id_search_add_users').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_user = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'ausers' tag    */
        Y.one('#id_ausers').get('options').each( function(index) {
            if (this.ancestor('ausers')) {
                this.unwrap();
                this.show();
            }//if_ausers_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_ausers").get("options").each( function() {
            var str_user = this.get('text').toLowerCase();

            if (str_user.indexOf('choose') == -1) {
                if (str_user.indexOf(find_user) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<ausers id="ausers_tag"></ausers>');
                }
            }
        });

        Y.one("#id_search_add_users").focus();
        window.onbeforeunload = null;
    });

    /* Search Remove Users  */
    Y.one('#id_search_sel_users').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_user = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'susers' tag    */
        Y.one('#id_susers').get('options').each( function(index) {
            if (this.ancestor('susers')) {
                this.unwrap();
                this.show();
            }//if_susers_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_susers").get("options").each( function() {
            var str_user = this.get('text').toLowerCase();

            if (str_user.indexOf('choose') == -1) {
                if (str_user.indexOf(find_user) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<susers id="susers_tag"></susers>');
                }
            }
        });

        Y.one("#id_search_sel_users").focus();
        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});