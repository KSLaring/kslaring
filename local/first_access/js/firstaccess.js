
YUI().use("node", "io", "event",'moodle-core-notification', function(Y) {
    var eid     = 0;
    var nodeid  = 0;

    // First access - index
    if (Y.one('#page-local-first_access-index')) {
        nodeid  = Y.one('#complete').get('id');
    }

    // First access - first access form
    if (Y.one('#page-local-first_access-first_access')) {
        nodeid  = Y.one('#id_submitbutton').get('id');
    }

    Y.all('a').on('click',function(e) {
        eid     = e.target.get('id');

        if ((eid !== nodeid) && (eid !== 'action-menu-toggle-0') && (eid !== 'actionmenuaction-3')) {
            onClick();
            e.preventDefault();
            e.stopPropagation();
            window.onbeforeunload = null;
        }
    });

    Y.all('button').on('click',function(e) {
        eid     = e.target.get('id');

        if ((eid !== nodeid) && (eid !== 'action-menu-toggle-0') && (eid !== 'actionmenuaction-3')) {
            onClick();
            e.preventDefault();
            e.stopPropagation();
            window.onbeforeunload = null;
        }
    });

    // Deactivate buttons browser
    history.pushState(null, null, document.URL);
    window.addEventListener('popstate', function () {
        history.pushState(null, null, document.URL);
        onClick();
    });


    var onClick = function(e) {
        alert(M.util.get_string('completeprofile','local_first_access'));
    };
});


