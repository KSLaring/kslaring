/**
 * Invoice Enrolment - Javascript
 *
 * Description
 *
 * @package         enrol
 * @subpackage      invoice/js
 * @copyright       2014 eFaktor
 *
 * @creationDate    25/09/2014
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    var zero;
    var one;
    var two;
    var three;

    /* ACCOUNT INVOICE  */
    if (Y.one('#id_invoice_type_ACCOUNT').get('checked')) {
        /* Activate Account Invoice     */
        Y.one('#id_resp_number').removeAttribute('disabled');
        Y.one('#id_resp_number').set('className','required account');
        Y.one('#id_service_number').removeAttribute('disabled');
        Y.one('#id_service_number').set('className','required account');
        Y.one('#id_project_number').removeAttribute('disabled');
        Y.one('#id_project_number').set('className','required account');
        Y.one('#id_act_number').removeAttribute('disabled');
        Y.one('#id_act_number').set('className','required account');
        Y.one('#id_resource_number').removeAttribute('disabled');
        Y.one('#id_resource_number').set('className','required account');

        /* Disabled Address Invoice     */
        Y.one('#id_street').setAttribute('disabled');
        Y.one('#id_post_code').setAttribute('disabled');
        Y.one('#id_city').setAttribute('disabled');
        Y.one('#id_bil_to').setAttribute('disabled');

        window.onbeforeunload = null;
    }//account_checked
    Y.one('#id_invoice_type_ACCOUNT').on('click', function (e) {

        /* Activate Account Invoice     */
        Y.one('#id_resp_number').removeAttribute('disabled');
        Y.one('#id_service_number').removeAttribute('disabled');
        Y.one('#id_project_number').removeAttribute('disabled');
        Y.one('#id_act_number').removeAttribute('disabled');
        Y.one('#id_resource_number').removeAttribute('disabled');

        /* Disabled Address Invoice     */
        Y.one('#id_street').setAttribute('disabled');
        Y.one('#id_post_code').setAttribute('disabled');
        Y.one('#id_city').setAttribute('disabled');
        Y.one('#id_bil_to').setAttribute('disabled');

        window.onbeforeunload = null;
    });

    /* ADDRESS INVOICE  */
    if (Y.one('#id_invoice_type_ADDRESS').get('checked')) {
        /* Activate Address Invoice     */
        Y.one('#id_street').removeAttribute('disabled');
        Y.one('#id_post_code').removeAttribute('disabled');
        Y.one('#id_city').removeAttribute('disabled');
        Y.one('#id_bil_to').removeAttribute('disabled');

        /* Disabled Account Invoice     */
        Y.one('#id_resp_number').setAttribute('disabled');
        Y.one('#id_service_number').setAttribute('disabled');
        Y.one('#id_project_number').setAttribute('disabled');
        Y.one('#id_act_number').setAttribute('disabled');
        Y.one('#id_resource_number').setAttribute('disabled');

        window.onbeforeunload = null;
    }//address_checked
    Y.one('#id_invoice_type_ADDRESS').on('click', function (e) {
        /* Activate Address Invoice     */
        Y.one('#id_street').removeAttribute('disabled');
        Y.one('#id_post_code').removeAttribute('disabled');
        Y.one('#id_city').removeAttribute('disabled');
        Y.one('#id_bil_to').removeAttribute('disabled');

        /* Disabled Account Invoice     */
        Y.one('#id_resp_number').setAttribute('disabled');
        Y.one('#id_service_number').setAttribute('disabled');
        Y.one('#id_project_number').setAttribute('disabled');
        Y.one('#id_act_number').setAttribute('disabled');
        Y.one('#id_resource_number').setAttribute('disabled');
        
        window.onbeforeunload = null;
    });

    if (Y.one('#id_lnk_search')) {
        Y.one('#id_lnk_search').on('click', function (e) {
            /* Get Level Zero   */
            if (Y.one("#id_level_0").get('value') != 0) {
                //Getting information of user.
                zero =Y.one("#id_level_0").get('value');
            }else {
                zero = 0;
            }

            /* Get Level One    */
            if (Y.one("#id_level_1").get('value') != 0) {
                //Getting information of user.
                one =Y.one("#id_level_1").get('value');
            }else {
                one = 0;
            }

            /* Get Level Two    */
            if (Y.one("#id_level_2").get('value') != 0) {
                //Getting information of user.
                two =Y.one("#id_level_2").get('value');
            }else {
                two = 0;
            }

            /* Get Level Three  */
            if (Y.one("#id_level_3").get('value') != 0) {
                //Getting information of user.
                three =Y.one("#id_level_3").get('value');
            }else {
                three = 0;
            }

            document.cookie = "level_0" + "=" + zero;
            document.cookie = "level_1" + "=" + one;
            document.cookie = "level_2" + "=" + two;
            document.cookie = "level_3" + "=" + three;

            window.onbeforeunload = null;
        });
    }//lkn_search

    window.onbeforeunload = null;
});