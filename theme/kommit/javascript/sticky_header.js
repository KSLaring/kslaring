//javascript sticky header
// when you scroll to top the nav bar sticks at teh top of the page
// it adds a css a css class "sticky" to DOM element "header"
// Jon Jack
// 26/03/2014
// -------------------------------------------------------------

window.onscroll=function () {
    var top = window.pageXOffset ? window.pageXOffset : document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
    if(top > 100){
        document.getElementById("header").className = 'sticky';
    }
    else {
        document.getElementById("header").className = '';
    }
};

