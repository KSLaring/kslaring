YUI().use('node', function(Y) {

    Y.delegate('click', function(e) {
        var buttonID = e.currentTarget.get('id');

        /* Collapse/Expand  */
        var idNode = buttonID + '_div';
        node = Y.one('#' + idNode);
        node.toggleView();

        /* Change the image */
        var idImg = buttonID + '_img';
        imgNode = Y.one('#' + idImg);
        var src = imgNode.get('src');
        if (src.indexOf('expanded.png') != -1) {
            imgNode.set('src',src.replace('expanded.png','collapsed.png'));
        }else {
            imgNode.set('src',src.replace('collapsed.png','expanded.png'));
        }//if_else
    },document, 'button');
});
