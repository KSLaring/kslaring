<?php

ini_set('display_errors','On');
ini_set('error_reporting', 'E_ALL');

include '../inc/db-conn.php';
include '../inc/db-layer.php';

$courses = getCourses($_GET['course']);

foreach ($courses as $course)
{
    print 'Test: '.$course['fullname'];
}

