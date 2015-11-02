<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 02/11/15
 * Time: 10:57
 * To change this template use File | Settings | File Templates.
 */

require_once('../../config.php');
require_once('adfslib.php');


$urlKS = KS_ADFS::LogIn_UserADFS(4);

header('Location: ' . urldecode($urlKS));
die;
