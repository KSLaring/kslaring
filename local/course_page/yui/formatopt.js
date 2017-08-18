YUI().use("node","event",'moodle-core-notification', function(Y) {
    // Homepage format option
    if (Y.one('#id_homepage')) {
        Y.one('#id_homepage').on('change', function (e) {
            document.cookie = "homepage_changed" + "=" + 1;
        });
    }

    // Ratings format option
    if (Y.one('#id_ratings')) {
        Y.one('#id_ratings').on('change', function (e) {
            document.cookie = "ratings_changed" + "=" + 1;
        });
    }

    // participant format option
    if (Y.one('#id_participant')) {
        Y.one('#id_participant').on('change', function (e) {
            document.cookie = "participant_changed" + "=" + 1;
        });
    }
});
