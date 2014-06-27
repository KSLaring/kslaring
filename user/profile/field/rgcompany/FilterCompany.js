/**
 * Created with JetBrains PhpStorm.
 * User: eFaktor    (fbv)
 * Date: 28.02.14
 * Time: 09:50
 * To change this template use File | Settings | File Templates.
 */

YUI().use('node', function(Y) {
    Y.one('#id_input_rgcompany').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_Company = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'rgCompany' tag    */
        Y.one('#id_profile_field_rgcompany').get('options').each( function(index) {
            if (this.ancestor('rgcompany')) {
                this.unwrap();
                this.show();
            }//if_rgcompany_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_profile_field_rgcompany").get("options").each( function() {
            var str_company = this.get('text').toLowerCase();

            if (str_company.indexOf('choose') == -1) {
                if (str_company.indexOf(find_Company) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<rgcompany id="rgcompany_tag"></rgcompany>');
                }
            }
        });

        Y.one("#id_input_rgcompany").focus();
        window.onbeforeunload = null;
    });
});
