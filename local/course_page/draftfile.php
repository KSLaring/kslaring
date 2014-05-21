<?php
/**
 * Local Course Home PAge Generator  - Draft File
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    10/06/2013
 * @author          efaktor     (fbv)
 */
define('NO_DEBUG_DISPLAY', true);

require_once('../../config.php');
require_once('../../lib/filelib.php');

$relativepath = get_file_argument();
$preview = optional_param('preview', null, PARAM_ALPHANUM);


// relative path must start with '/'
if (!$relativepath) {
    print_error('invalidargorconf');
} else if ($relativepath{0} != '/') {
    print_error('pathdoesnotstartslash');
}

// extract relative path components
$args = explode('/', ltrim($relativepath, '/'));

if (count($args) == 0) { // always at least user id
    print_error('invalidarguments');
}

$contextid = (int)array_shift($args);
$component = array_shift($args);
$filearea  = array_shift($args);
$draftid   = (int)array_shift($args);

if ($component !== 'course' && ($filearea !== 'pagegraphics' or $filearea !== 'pagevideo')) {
    send_file_not_found();
}

$context = context::instance_by_id($contextid);

$fs = get_file_storage();

$relativepath = implode('/', $args);
$fullpath = "/$context->id/course/$filearea/$draftid/$relativepath";

if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->get_filename() == '.') {
    send_file_not_found();
}

// ========================================
// finally send the file
// ========================================
\core\session\manager::write_close(); // Unlock session during file serving.
send_stored_file($file, 0, false,false, array('preview' => $preview)); // force download - security first!