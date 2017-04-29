/*global M*/
M.local_lessonexport = M.local_lessonexport || {};
M.local_lessonexport.printlinks = {
    init: function(links) {
        var el, parent, i;

        // Find the right place in the DOM to add the links.
        try {
            el = Y.one('#region-main>[role="main"]')
            el = el.one('#maincontent');
            // if (el.next('#maincontent')) {
            //     el = el.next('#maincontent');
            // }
            el = el.next();
            parent = el.ancestor();
        } catch (e) {
            // The correct location to add the links was not found.
            return;
        }

        for (i in links) {
            if (!links.hasOwnProperty(i)) {
                continue;
            }
            parent.insert(links[i], el);
        }
    }
};