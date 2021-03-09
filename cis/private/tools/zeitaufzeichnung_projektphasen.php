<?php

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/projektphase.class.php');
require_once('../../../include/datum.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
$user = get_uid();

$datum_obj = new datum();

if(isset($_GET['projekt_kurzbz'])) // TODO maybe check that phasen only shown if projekt is projekt of logged in user
{
	$projekt_kurzbz = $_GET['projekt_kurzbz'];
	$projektphase = new projektphase();

	if($projektphase->getProjectphaseForMitarbeiterByKurzBz($user, $projekt_kurzbz))
		$projektphasen_user = $projektphase->result;
	else
		$projektphasen_user = array();

	$pp_user_ids = array();
	foreach ($projektphasen_user as $pp_user)
	{
		array_push($pp_user_ids, $pp_user->projektphase_id);
	}

	if($projektphase->getProjektphasen($projekt_kurzbz))
	{
		$result_obj = array();
		foreach($projektphase->result as $row)
		{
			if(in_array($row->projektphase_id, $pp_user_ids))
			{
				$item['projektphase_id'] = $row->projektphase_id;
				$item['bezeichnung'] = $row->bezeichnung;
				$item['start'] = $datum_obj->formatDatum($row->start, 'd.m.Y');
				$item['ende'] = $datum_obj->formatDatum($row->ende, 'd.m.Y');
				$result_obj[] = $item;
			}
		}
		echo json_encode($result_obj);
	}
	exit;
}
