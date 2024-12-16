<?php

require_once('../config/vilesci.config.inc.php');
require_once('../include/lehrveranstaltung_faktor.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiensemester.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/person', null, 'suid'))
{
	exit('Sie haben keine Berechtigung für die Seite');
}

$method = isset($_REQUEST['method']) ? $_REQUEST['method']: '' ;
$lv_faktor = new lehrveranstaltung_faktor();

switch($method)
{
	case 'addFaktor':
		$faktor = isset($_REQUEST['faktor']) ? $_REQUEST['faktor']: '' ;
		if ($faktor !== '')
		{
			if (!isRightType($faktor['lv_id']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Nur LVs und Templates möglich'
				]);
				break;
			}
			if (vonHigherThanBis($faktor['von'], $faktor['bis']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Von nach Bis'
				]);
				break;
			}
			if (exists($faktor['lv_id'], $faktor['von'], $faktor['bis']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Für den Zeitraum bereits vorhanden'
				]);
				break;
			}

			$result = $lv_faktor->addFaktor($faktor['lv_id'], $faktor['faktor'], $faktor['von'], $faktor['bis']);
			echo json_encode($result);
		}
		break;
	case 'updateFaktor':
		$faktor = isset($_REQUEST['faktor']) ? $_REQUEST['faktor']: '' ;
		if ($faktor !== '')
		{
			if (vonHigherThanBis($faktor['von'], $faktor['bis']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Von nach Bis'
				]);
				break;
			}
			if (exists($faktor['lv_id'], $faktor['von'], $faktor['bis'], $faktor['id']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Für den Zeitraum bereits vorhanden'
				]);
				break;
			}

			$result = $lv_faktor->updateFaktor($faktor['id'], $faktor['faktor'], $faktor['von'], $faktor['bis']);
			echo json_encode($result);
		}
		break;
	case 'deleteFaktor':
		$faktor = isset($_REQUEST['faktor']) ? $_REQUEST['faktor']: '' ;
		if ($faktor !== '')
		{
			$result = $lv_faktor->deleteFaktor($faktor['id']);
			echo json_encode($result);
		}
 		break;
	default:
		break;
}

function isRightType($lv_id)
{
	$lv = new lehrveranstaltung($lv_id);
	if (in_array($lv->lehrtyp_kurzbz, array('lv', 'tpl')))
		return true;

	return false;
}

function exists($lv_id, $von, $bis, $id = null)
{
	$lv_faktor = new lehrveranstaltung_faktor();
	$lv_faktor->loadByLV($lv_id, $von, $bis, $id);
	return !empty($lv_faktor->lv_faktoren);
}

function vonHigherThanBis($von, $bis)
{
	$vonStsem = new studiensemester($von);
	$bisStsem = new studiensemester($bis);

	if (is_null($bis) || $bis === "")
		return false;
	if ($vonStsem->start > $bisStsem->start)
		return true;
	else
		return false;

}

?>
