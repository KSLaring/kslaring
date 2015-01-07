/**
 * Created with JetBrains PhpStorm.
 * User: fbv
 * Date: 01.04.14
 * Time: 14:13
 * To change this template use File | Settings | File Templates.
 */
YUI().use('node', function(Y) {

    Y.delegate('click', function(e) {
        if (e.currentTarget.get('id') == 'ASC') {
            e.currentTarget.set('id','DESC');
        }else {
            e.currentTarget.set('id','ASC');
        }
        document.cookie = "dir" + "=" + e.currentTarget.get('id');

        window.onbeforeunload = null;
        window.location = location.href;
    },document, 'button');
});