<?php

function local_background_image_pluginfile($course = 1, $cm = null, $context, $filearea, $args, $forcedownload, $preview) {
    $filename = array_pop($args);
    $filepath = '/';
    $fs = get_file_storage();
    if (!$file = $fs->get_file($context->id, 'local_background_image', 'picture', 1, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    \core\session\manager::write_close(); // Unlock session during file serving.
    send_stored_file($file, 60*60, 0, $forcedownload, $preview);
}
