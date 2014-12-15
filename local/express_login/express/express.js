/**
 * Created with JetBrains PhpStorm.
 * User: paqui
 * Date: 28/11/14
 * Time: 12:43
 * To change this template use File | Settings | File Templates.
 */


var client      = new ZeroClipboard(document.getElementById("id_btn_copy_link"));

var divClip     = document.getElementById('clipboardDiv');
var bookmarkDiv = document.getElementById('bookmarkDiv');

client.on( "ready", function( readyEvent ) {
    var urlBook     = Y.one('#id_btn_copy_link').getAttribute('data-clipboard-text');
    var newContent  = bookmarkDiv.innerHTML.valueOf();

    client.on( "aftercopy", function( event ) {
        divClip.style.display = 'block';
        newContent = bookmarkDiv.innerHTML.replace('#',urlBook);
        bookmarkDiv.innerHTML = newContent;
        bookmarkDiv.style.display = 'block';
    } );
});

