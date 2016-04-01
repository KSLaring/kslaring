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
                id : 'ratings',
                headerContent: params['header'],
                bodyContent:params['content'],
                draggable: true,
                visible: true,
                modal: true,
                render:true,
                width: '80%'
            });

            Y.one('#ratings').setStyle('left','25px');
            Y.one('#ratings').setStyle('bottom','-50px');
            Y.one('#ratings').setStyle('width','80%');
            Y.one('#location').setStyle('position','fixed');

            panel.show();

        },Y.one(document.body), '#show');
    }
});