<?php
define('SAML_INTERNAL', 1);

// In order to avoid session problems we first do the SAML issues and then
// we log in and register the attributes of user

try{
    global $CFG;
    // We read saml parameters from a config file instead from the database
    // due we can not operate with the moodle database without load all
    // moodle session issue.
    if(file_exists($CFG->dataroot.'/saml_config.php')) {
        $contentfile = file_get_contents($CFG->dataroot.'/saml_config.php');
    }
    else if (file_exists('saml_config.php')) {
        $contentfile = file_get_contents('saml_config.php');
    } else {
        throw(new Exception('SAML config params are not set.'));
    }

    $saml_param = json_decode($contentfile);

    if(!file_exists($saml_param->samllib.'/_autoload.php')) {
        throw(new Exception('simpleSAMLphp lib loader file does not exist: '.$saml_param->samllib.'/_autoload.php'));
    }
    include_once($saml_param->samllib.'/_autoload.php');
    $as = new SimpleSAML_Auth_Simple($saml_param->sp_source);

    /* Plugin Info */
    $pluginInfo = get_config('local_feide');
    /* Make KS URL Response */
    $urltogo = $pluginInfo->ks_point . "/local/wsks/feide/logout.php";
    $urltogo = 'http://www.elpuntavui.cat';

    if ($saml_param->dosinglelogout) {
        $as->logout($urltogo);
        assert("FALSE"); // The previous line issues a redirect
    }else {
        header('Location: '. $urltogo);
        exit();
    }


    $as->requireAuth();
    $valid_saml_session = $as->isAuthenticated();
    $saml_attributes = $as->getAttributes();
}catch (Exception $e) {
    session_write_close();
    require_once('../../config.php');
    require_once('error.php');

    global $err, $PAGE, $OUTPUT;
    $PAGE->set_url('/auth/saml/index.php');
    $PAGE->set_context(CONTEXT_SYSTEM::instance());

    $pluginconfig = get_config('auth/saml');
    $urltogo = $CFG->wwwroot;
    if($CFG->wwwroot[strlen($CFG->wwwroot)-1] != '/') {
        $urltogo .= '/';
    }

    $err['login'] = $e->getMessage();
    auth_saml_log_error('Moodle SAML module:'. $err['login'], $pluginconfig->samllogfile);;
    auth_saml_error($err['login'], $urltogo, $pluginconfig->samllogfile);
}

// Now we close simpleSAMLphp session
session_write_close();