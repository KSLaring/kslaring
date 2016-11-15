<?php

include "db-conn.php";

function getUsers($course)
{
    $course_list = array();
    
    $sql = "SELECT person.personID, person.fornavn, person.etternavn, kursinstans.kursinstans, paamelding.kursinstansID FROM person JOIN paamelding ON person.PersonID = paamelding.PersonID JOIN kursinstans ON paamelding.KursinstansID = kursinstans.KursinstansID WHERE memberOf = '$course'"; 
    
    $result = mysql_query($sql) or die("mysql error: ". mysql_error());
    
    while ($row = mysql_fetch_assoc($result))
    {
        $course_list[] = $row;
    }
    
    return $course_list;
}



