/**
 * Report Manager - Javascript
 *
 * Description
 *
 * @package         report
 * @subpackage      Manager
 * @copyright       2012 eFaktor
 *
 * @creationDate    11/09/2012
 * @author          eFaktor     (fbv)
 *
 * @updateDate      26/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the level Zero and One
 */
function saveOutcome(outcome) {
    /* Variables */
    var out_come;

    out_come = document.getElementsByName(outcome)[0].value;
    document.cookie = "outcomeReport" + "=" + out_come;

    window.onbeforeunload = null;
    window.location = location.href;
}//saveOutcome

function saveCourse(course) {
    /* Variables */
    var co;

    co = document.getElementsByName(course)[0].value;
    document.cookie = "courseReport" + "=" + co;

    window.onbeforeunload = null;
    window.location = location.href;
}//saveCourse

function saveJobRole(job_role){
    /* Variables */
    var jobrole;

    jobrole = document.getElementsByName(job_role)[0].value;
    document.cookie = "employeeReport" + "=" + jobrole;

    window.onbeforeunload = null;
    window.location = location.href;
}