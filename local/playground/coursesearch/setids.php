<?php

$fname = __DIR__ . '/tags.json';
$json = file_get_contents($fname);

$counter = 1;
$anynumberpattern = '/"id": \d{1,}/';
$nullpattern = '/"id": 0/';

$json = preg_replace($anynumberpattern, '"id": 0', $json);

while (preg_match($nullpattern, $json)) {
    $json = preg_replace($nullpattern, '"id": ' . $counter++, $json, 1);
}

file_put_contents($fname, $json);
