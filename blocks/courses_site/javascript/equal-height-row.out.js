/**
 * Created by urshunkler2 on 2014-11-17.
 */
/* Thanks to CSS Tricks for pointing out this bit of jQuery
 http://css-tricks.com/equal-height-blocks-in-rows/
 It's been modified into a function called at page load and then each time the page is resized.
 One large modification was to remove the set height before each new calculation. */

/*global console:false */

window.equalheight = function (container) {
    var currentTallest = 0,
        currentRowStart = 0,
        rowDivs = [],
        $el,
        topPosition = 0,
        eleHeight = 0,
        eleInnerHeight = 0,
        heightDiff = 0,
        currentDiv;

    // With each featured course block calculate the highest block in a row and set
    // all blocks in a row to that height.
    $(container).each(function () {
        $el = $(this);
        $el.height('auto');
        $el.find('.course-type').css('margin-top', 0);
        topPosition = $el.position().top;

        // Get the tallest block
        if (currentRowStart !== topPosition) {
            // When the row changes set the height for all row blocks
            for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                rowDivs[currentDiv].height(currentTallest);
            }
            //rowDivs.length = 0; // empty the array
            rowDivs = []; // empty the array
            currentRowStart = topPosition;
            currentTallest = $el.height();
            rowDivs.push($el);
        } else {
            rowDivs.push($el);
            currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
        }

        // Adjust all blocks in the row
        for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
            rowDivs[currentDiv].height(currentTallest);
        }
    });

    $(container).each(function () {
        $el = $(this);

        // add a top-margin to the button/icon row to align the row to the bottom
        eleHeight = $el.height();
        eleInnerHeight = $el.find('.bcs-course-inner').height();

        if (eleInnerHeight < eleHeight) {
            heightDiff = eleHeight - eleInnerHeight;
            $el.find('.course-type').css('margin-top', heightDiff + 'px');
        }
    });
};


