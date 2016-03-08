<?php

//define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');

$model = new local_efaktor_model();
//$model->set_datalist_data('friadmin_courselist', 'data');

$fields = array('name', 'date', 'seats', 'municipality', 'sector', 'location');
$model->set_fixture_data(
    $CFG->dirroot . '/local/efaktor/fixtures/friadmin_courselist.json', 'data', $fields);

/* @var local_efaktor_datalist $mdata */
$mdata = $model->datalist;

$d = new DateTime('2015-05-01');
$d2 = new DateTime('2015-07-01');
$mdata->where('date', '>= ' . $d->getTimestamp());
$mdata->where('date', '<= ' . $d2->getTimestamp());
$mdata->find();

@header('Content-type: text/html; charset=utf-8');

//echo '<pre>'.var_export($mdata->sort(array('municipality', 'sector', 'name')), true).'</pre>';
//echo '<pre>'.var_export($mdata->sort('name', 'DESC'), true).'</pre>';
echo '<pre>'.var_export($mdata->sort(), true).'</pre>';
