/**
 * Course Home Page  - Renderer - Show Location Info - Lightbox Panel
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2016
 * @author          eFaktor     (fbv)
 */

YUI.add('moodle-local_course_page-location', function(Y) {

    M.local_course_page = M.local_course_page || {};
    M.local_course_page.location = function(params) {
        var self = this;
        Y.delegate('click', function(e){
            var panel = new M.core.dialogue({
                id: 'location',
                headerContent: params['header'],
                bodyContent:params['content'],
                draggable: true,
                visible: true,
                center: false,
                modal: true,
                render:true,
                width: '70%'
            });

            Y.one('#location').setStyle('left','25px');
            Y.one('#location').setStyle('top','50px');
            Y.one('#location').setStyle('bottom','0px');
            Y.one('#location').setStyle('width','70%');
            Y.one('#location').setStyle('position','fixed');

            panel.show();
        },Y.one(document.body), '#show_location');
    };
});