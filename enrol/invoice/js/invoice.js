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

        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});