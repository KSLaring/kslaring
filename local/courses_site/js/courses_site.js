/**
 * Local Courses Site - Javascript
 *
 * @package         local
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    23/05/2014
 * @author          efaktor     (fbv)
 */

function getCategory(parent){
    /* Defining variables. */
    var parentCategory;

    //Getting information of user.
    parentCategory = document.getElementsByName(parent)[0].value;

    //Save information in cookie
    document.cookie = "parentCategory"  + "=" + parentCategory;

    window.onbeforeunload = null;
    window.location = location.href;
}//get_category