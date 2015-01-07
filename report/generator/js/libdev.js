/**
 * Report Generator - Javascript
 *
 * Description
 *
 * @package         report
 * @subpackage      generator
 * @copyright       2012 eFaktor
 *
 * @creationDate    11/09/2012
 * @author          eFaktor     (fbv)
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

function GetLevelTwo(parent) {
    /* Defining variables. */
    var parentLevelOne;
    var parentLevelTwo;
    var parentLevelTree;

    //Getting information of user.
    parentLevelOne = document.getElementsByName(parent)[0].value;

    //Save information in cookie
    document.cookie = "parentLevelOne"  + "=" + parentLevelOne;
    document.cookie = "parentLevelTwo"  + "=0";
    document.cookie = "parentLevelTree" + "=0";

    window.onbeforeunload = null;
    window.location = location.href;
}//getLevelTwo

function GetLevelTree(parent) {
    /* Defining variables. */
    var parentLevelTwo;
    var parentLevelTree;

    //Getting information of user.
    parentLevelTwo = document.getElementsByName(parent)[0].value;

    //Save information in cookie
    document.cookie = "parentLevelTwo"  + "=" + parentLevelTwo;
    document.cookie = "parentLevelTree" + "=0";

    window.onbeforeunload = null;
    window.location = location.href;
}//getLevelThree

function GetLevelEmployee(parent){
    /* Defining variables. */
    var parentLevelTree;

    //Getting information of user.
    parentLevelTree = document.getElementsByName(parent)[0].value;

    //Save information in cookie
    document.cookie = "parentLevelTree" + "=" + parentLevelTree;

    window.onbeforeunload = null;
    window.location = location.href;
}//GetLevelEmployee

function getLevelImport(level,url){
    var var_url;
    var parent_level;

    /* Get the new level    */
    parent_level     = document.getElementsByName(level)[0].value;

    var_url = url + '?level=' + parent_level;

    window.onbeforeunload = null;
    window.location = var_url;
}//getLevelImport

function getParentTwoImport(parent) {
    /* Defining variables. */
    var parentImportTwo;

    //Getting information of user.
    parentImportTwo = document.getElementsByName(parent)[0].value;

    //Save information in cookie
    document.cookie = "parentImportTwo"  + "=" + parentImportTwo;

    window.onbeforeunload = null;
    window.location = location.href;
}//getParentTwoImport

function saveJobRole(job_role){
    /* Variables */
    var jobrole;

    jobrole = document.getElementsByName(job_role)[0].value;
    document.cookie = "employeeReport" + "=" + jobrole;

    window.onbeforeunload = null;
    window.location = location.href;
}