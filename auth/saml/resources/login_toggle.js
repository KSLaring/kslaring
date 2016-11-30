
YUI().use('node', function(Y) {


    Y.one('.loginpanel .loginsub').hide();

    Y.delegate('click', function(e) {
    var buttonID = e.currentTarget.get('id'),
    node = Y.one('.loginpanel .loginsub');

    if (buttonID === 'show') {
    node.show();
    } else if (buttonID === 'hide') {
    node.hide();
    } else if (buttonID === 'toggle') {
    node.toggleView();
    }

}, document, 'button');
});

Y.on("domready", function () {
    Y.one('.loginpanel .loginsub').hide();
});
