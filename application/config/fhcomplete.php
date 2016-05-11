<?php  
	if (! defined('BASEPATH'))
		exit('No direct script access allowed');

$config['fhc_version'] = '3.2';

$config['fhc_acl'] = array
(
	'bis.tbl_bundesland' => 'basis/nation',
	'bis.tbl_nation' => 'basis/nation',
	'bis.tbl_lgartcode' => 'basis/lgartcode',
	
	'campus.tbl_dms' => 'basis/tbl_dms',
	'campus.tbl_dms_version' => 'basis/tbl_dms_version',
	
	'lehre.tbl_studienplan' => 'basis/studienplan',
	'lehre.tbl_studienordnung' => 'basis/studienordnung',
	'lehre.vw_studienplan' => 'basis/vw_studienplan',
	
	'public.tbl_adresse' => 'basis/adresse',
	'public.tbl_person' => 'basis/person',
	'public.tbl_kontakt' => 'basis/kontakt',
	'public.tbl_benutzer' => 'basis/benutzer',
	'public.tbl_prestudent' => 'basis/person',
	'public.tbl_prestudentstatus' => 'basis/person',
	'public.tbl_organisationseinheit' => 'basis/organisationseinheit',
	'public.tbl_sprache' => 'admin',
	'public.tbl_msg_thread' => 'admin',
	'public.tbl_msg_message' => 'admin'
);