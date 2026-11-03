<?php

$config['send_update_mails'] = false;
$config['calendar_start'] = 7;
$config['calendar_end'] = 23;


$config['tempus_tags'] = [
	'prioone' => ['readonly' => false],
	'priotwo' => ['readonly' => true],
	'hinweis' => ['readonly' => false],
];
$config['tempus_issue_fallback_oe'] = 'etw';
$config['tempus_azg_oe'] = 'etw';
$config['tempus_azg_max_stunden_pro_tag'] = 12;
$config['tempus_azg_ausgenommene_studiengaenge'] = array(10020, 11000, 10004);
$config['tempus_azg_ausgenommene_orte'] = array('EXTERN1', 'EXTERN2', 'EXTERN3', 'EXKURSION');
$config['tempus_azg_ausgenommene_uids'] = array('_DummyLektor');