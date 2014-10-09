/**
 * Force Profile - Javascript
 *
 * Description
 *
 * @package         local
 * @subpackage      force_profile
 * @copyright       2014 eFaktor
 *
 * @creationDate    21/08/2014
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    /* JOB ROLES    */
    Y.one('#id_county').on('change', function (e) {
        var county      = Y.one('#id_county').get('value');

        /* Clean List - Municipalities   */
        Y.one('#id_munis').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                this.remove();
            }
        });

        /* Clean the list   - Job Roles */
        Y.one('#id_profile_field_rgjobrole').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                this.remove();
            }
        });

        /* Show Municipalities connected with the county*/
        Y.one('#id_munis_hidden').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                var id_county = opt.substr(0,2);
                if (id_county == county) {
                    Y.one('#id_munis').appendChild(this);
                }
            }
        });

        Y.one("#id_munis").focus();

        window.onbeforeunload = null;
    });

    /* JOB ROLES - MUNIS    */
    Y.one('#id_munis').on('change', function (e) {
        var muni = Y.one('#id_munis').get('value');

        /* Clean the list   */
        Y.one('#id_profile_field_rgjobrole').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                this.remove();
            }
        });

        /* Show Job Roles connected with Municipality   */
        Y.one('#id_jr_hidden').get('options').each(function(){
            var job_role = this.get('value');

            if (job_role != 0) {
                if (job_role.indexOf(muni + '_') != -1) {
                    Y.one('#id_profile_field_rgjobrole').appendChild(this);
                    Y.one('#id_profile_field_rgjobrole').removeAttribute('disabled');
                }
            }
        });

        window.onbeforeunload = null;
    });

    /* Save new Job Role */
    Y.one('#id_profile_field_rgjobrole').on('change', function (e) {
        var sel_jr = '';

        Y.one('#id_profile_field_rgjobrole').get('options').each(function(){
            if (this.get('selected')) {
                if (sel_jr == '') {
                    sel_jr = this.get('value');
                }else {
                    sel_jr = sel_jr + ',' + this.get('value');
                }//if_sel_jr
            }//if_selected
        });

        if (sel_jr != '') {
            Y.one('#id_jr_id').set('value',sel_jr);
        }

        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});
