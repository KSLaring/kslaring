YUI().use('node', function(Y) {

    if (Y.one('#id_type_1').getAttribute('checked') == 'checked') {

        Y.one('#id_industrycode').removeAttribute('disabled');

        Y.one('#id_level_0').setAttribute('disabled', 'disabled');
        Y.one('#id_level_1').setAttribute('disabled', 'disabled');
        Y.one('#id_level_2').setAttribute('disabled', 'disabled');
        Y.one('#id_level_3').setAttribute('disabled', 'disabled');

    } else if (Y.one('#id_type_0').getAttribute('checked') == 'checked') {

        Y.one('#id_industrycode').setAttribute('disabled', 'disabled');

        Y.one('#id_level_0').removeAttribute('disabled');
    }

    Y.one('#id_type_1').on('click', function (e) {

        Y.one('#id_industrycode').removeAttribute('disabled');

        Y.one('#id_level_0').setAttribute('disabled', 'disabled');
        Y.one('#id_level_1').setAttribute('disabled', 'disabled');
        Y.one('#id_level_2').setAttribute('disabled', 'disabled');
        Y.one('#id_level_3').setAttribute('disabled', 'disabled');

        window.onbeforeunload = null;
    });

    Y.one('#id_type_0').on('click', function (e) {

        Y.one('#id_industrycode').setAttribute('disabled', 'disabled');

        Y.one('#id_level_0').removeAttribute('disabled');

        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});