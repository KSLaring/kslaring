/**
 * Course Home Page  - Renderer - Show Reviews - Lightbox Panel
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    09/07/2014
 * @author          eFaktor     (fbv)
 */

YUI.add('moodle-local_course_page-ratings', function(Y) {


    M.local_course_page = M.local_course_page || {};
    M.local_course_page.ratings = function(params) {
        var self = this;
        Y.delegate('click', function(e){
            var panel = new M.core.dialogue({
                toolbars: false,
                modal: true,
                headerContent: params['header'],
                bodyContent:params['content'],
                visible: true, //by default it is not displayed
                lightbox : true,
                closeButtonTitle: 'Close',
                width: 800
            });

            panel.show();

        },Y.one(document.body), '#show');
    }
});